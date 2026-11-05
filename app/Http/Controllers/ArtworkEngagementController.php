<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Notifications\ArtfyDatabaseNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArtworkEngagementController extends Controller
{
    public function like(Request $request, Artwork $artwork): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isArtLover(), 403);
        $this->ensurePublicArtwork($artwork);

        DB::transaction(function () use ($user, $artwork): void {
            $created = DB::table('artwork_likes')->insertOrIgnore([
                'artwork_id' => $artwork->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($created === 1 && $artwork->artist) {
                $artwork->artist->notify(new ArtfyDatabaseNotification(
                    'artwork_liked',
                    [
                        'title' => 'Your artwork received a like',
                        'message' => $user->name . ' liked "'
                            . $artwork->title . '".',
                        'url' => route('artworks.show', [
                            'slug' => 'uploaded-' . $artwork->id,
                        ], false),
                    ]
                ));
            }
        });

        return $this->backToArtwork($artwork, 'You liked this artwork.');
    }

    public function unlike(Request $request, Artwork $artwork): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isArtLover(), 403);
        $this->ensurePublicArtwork($artwork);

        DB::table('artwork_likes')
            ->where('artwork_id', $artwork->id)
            ->where('user_id', $user->id)
            ->delete();

        return $this->backToArtwork($artwork, 'You removed your like.');
    }

    public function comment(
        Request $request,
        Artwork $artwork
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user->isArtLover(), 403);
        $this->ensurePublicArtwork($artwork);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $artwork->comments()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        if ($artwork->artist) {
            $artwork->artist->notify(new ArtfyDatabaseNotification(
                'artwork_commented',
                [
                    'title' => 'Your artwork received a comment',
                    'message' => $user->name . ' commented on "'
                        . $artwork->title . '".',
                    'url' => route('artworks.show', [
                        'slug' => 'uploaded-' . $artwork->id,
                    ], false),
                ]
            ));
        }

        return $this->backToArtwork(
            $artwork,
            'Your comment has been posted.'
        );
    }

    private function ensurePublicArtwork(Artwork $artwork): void
    {
        abort_unless(
            in_array($artwork->status, ['available', 'sold'], true)
            && $artwork->moderation_status === 'approved',
            404
        );
    }

    private function backToArtwork(
        Artwork $artwork,
        string $message
    ): RedirectResponse {
        return redirect()
            ->route('artworks.show', [
                'slug' => 'uploaded-' . $artwork->id,
            ])
            ->with('success', $message);
    }
}
