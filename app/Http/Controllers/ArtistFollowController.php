<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ArtfyDatabaseNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArtistFollowController extends Controller
{
    public function follow(Request $request, User $artist): RedirectResponse
    {
        $follower = $request->user();
        abort_unless($follower->isArtLover(), 403);
        $this->ensurePublicArtist($artist);
        abort_unless($follower->isNot($artist), 403);

        DB::transaction(function () use ($follower, $artist): void {
            $created = DB::table('artist_follows')->insertOrIgnore([
                'follower_id' => $follower->id,
                'artist_id' => $artist->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($created === 1) {
                $followerName = $follower->name;

                $artist->notify(new ArtfyDatabaseNotification(
                    'artist_followed',
                    [
                        'title' => 'You have a new follower',
                        'message' => $followerName . ' started following you.',
                        'url' => route('artists.show', [
                            'slug' => 'artist-' . $artist->id,
                        ], false),
                    ]
                ));
            }
        });

        return redirect()
            ->route('artists.show', ['slug' => 'artist-' . $artist->id])
            ->with('success', 'You are now following this artist.');
    }

    public function unfollow(Request $request, User $artist): RedirectResponse
    {
        $follower = $request->user();
        abort_unless($follower->isArtLover(), 403);
        $this->ensurePublicArtist($artist);
        abort_unless($follower->isNot($artist), 403);

        $follower->following()->detach($artist->id);

        return redirect()
            ->route('artists.show', ['slug' => 'artist-' . $artist->id])
            ->with('success', 'You have unfollowed this artist.');
    }

    private function ensurePublicArtist(User $artist): void
    {
        abort_unless(
            $artist->isArtist()
            && $artist->artistApplication()
                ->where('status', 'approved')
                ->where('payment_status', 'verified')
                ->exists(),
            404
        );
    }
}
