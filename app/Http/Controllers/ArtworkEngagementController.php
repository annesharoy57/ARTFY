<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\ArtworkComment;
use App\Models\User;
use App\Notifications\ArtfyDatabaseNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArtworkEngagementController extends Controller
{
    public function toggleLike(
        Request $request,
        Artwork $artwork
    ): RedirectResponse {
        $this->ensurePublicArtwork($artwork);

        $user = $request->user();
        $alreadyLiked = $user->likedArtworks()
            ->whereKey($artwork->id)
            ->exists();

        if ($alreadyLiked) {
            $user->likedArtworks()->detach($artwork->id);
        } else {
            $user->likedArtworks()->syncWithoutDetaching([$artwork->id]);
            $this->notifyArtist(
                $artwork,
                $user,
                'artfy.artwork_liked',
                'Your artwork received a like',
                $user->name.' liked "'.$artwork->title.'".'
            );
        }

        return $this->returnToArtwork($request, $artwork);
    }

    public function toggleFavorite(
        Request $request,
        Artwork $artwork
    ): RedirectResponse {
        $this->ensurePublicArtwork($artwork);

        $user = $request->user();
        $alreadySaved = $user->savedArtworks()
            ->whereKey($artwork->id)
            ->exists();

        if ($alreadySaved) {
            $user->savedArtworks()->detach($artwork->id);
        } else {
            $user->savedArtworks()->syncWithoutDetaching([$artwork->id]);
            $this->notifyArtist(
                $artwork,
                $user,
                'artfy.artwork_saved',
                'Your artwork was saved',
                $user->name.' saved "'.$artwork->title.'" to their favorites.'
            );
        }

        return $this->returnToArtwork($request, $artwork);
    }

    public function storeComment(
        Request $request,
        Artwork $artwork
    ): RedirectResponse {
        $this->ensurePublicArtwork($artwork);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $comment = $artwork->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $this->notifyArtist(
            $artwork,
            $request->user(),
            'artfy.artwork_commented',
            'Your artwork received a comment',
            $request->user()->name.' commented on "'.$artwork->title.'".',
            ['comment_id' => $comment->id]
        );

        return $this->returnToArtwork($request, $artwork);
    }

    public function updateComment(
        Request $request,
        Artwork $artwork,
        ArtworkComment $comment
    ): RedirectResponse {
        $this->ensurePublicArtwork($artwork);
        abort_unless(
            $comment->artwork_id === $artwork->id
                && $comment->user_id === $request->user()->id
                && $comment->parent_id === null,
            404
        );

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $comment->update(['body' => $validated['body']]);

        return $this->returnToArtwork($request, $artwork);
    }

    public function reply(
        Request $request,
        Artwork $artwork,
        ArtworkComment $comment
    ): RedirectResponse {
        $this->ensurePublicArtwork($artwork);
        abort_unless(
            (int) $artwork->user_id === (int) $request->user()->id
                && $comment->artwork_id === $artwork->id
                && $comment->parent_id === null,
            404
        );

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $reply = $comment->replies()->create([
            'artwork_id' => $artwork->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        if ((int) $comment->user_id !== (int) $request->user()->id) {
            User::findOrFail($comment->user_id)->notify(
                new ArtfyDatabaseNotification(
                    'artfy.comment_replied',
                    [
                        'title' => 'The artist replied to your comment',
                        'message' => $artwork->artist?->name
                            .' replied to your comment on "'
                            .$artwork->title.'".',
                        'artwork_slug' => 'uploaded-'.$artwork->id,
                        'comment_id' => $reply->id,
                    ]
                )
            );
        }

        return $this->returnToArtwork($request, $artwork);
    }

    public function toggleCommentLike(
        Request $request,
        Artwork $artwork,
        ArtworkComment $comment
    ): RedirectResponse {
        $this->ensurePublicArtwork($artwork);
        abort_unless(
            $request->user()->isArtist()
                && (int) $artwork->user_id === (int) $request->user()->id
                && $comment->artwork_id === $artwork->id,
            403
        );

        $alreadyLiked = $comment->likes()
            ->whereKey($request->user()->id)
            ->exists();

        if ($alreadyLiked) {
            $comment->likes()->detach($request->user()->id);
        } else {
            $comment->likes()->syncWithoutDetaching([$request->user()->id]);

            if ((int) $comment->user_id !== (int) $request->user()->id) {
                User::findOrFail($comment->user_id)->notify(
                    new ArtfyDatabaseNotification(
                        'artfy.comment_liked',
                        [
                            'title' => 'The artist liked your comment',
                            'message' => $artwork->artist?->name
                                .' liked your comment on "'
                                .$artwork->title.'".',
                            'artwork_slug' => 'uploaded-'.$artwork->id,
                            'comment_id' => $comment->id,
                        ]
                    )
                );
            }
        }

        return $this->returnToArtwork($request, $artwork);
    }

    private function ensurePublicArtwork(Artwork $artwork): void
    {
        abort_unless(
            in_array($artwork->status, ['available', 'sold'], true)
                && $artwork->moderation_status === 'approved',
            404
        );
    }

    private function notifyArtist(
        Artwork $artwork,
        User $actor,
        string $type,
        string $title,
        string $message,
        array $extra = []
    ): void {
        if ((int) $artwork->user_id === (int) $actor->id) {
            return;
        }

        $artist = $artwork->artist;

        if (! $artist) {
            return;
        }

        $artist->notify(new ArtfyDatabaseNotification($type, array_merge([
            'title' => $title,
            'message' => $message,
            'artwork_slug' => 'uploaded-'.$artwork->id,
            'artwork_id' => $artwork->id,
        ], $extra)));
    }

    private function returnToArtwork(
        Request $request,
        Artwork $artwork
    ): RedirectResponse {
        $returnTo = $request->input('return_to');

        if (
            is_string($returnTo)
            && str_starts_with($returnTo, '/')
            && ! str_starts_with($returnTo, '//')
            && ! str_contains($returnTo, '\\')
            && ! preg_match('/[\x00-\x1F\x7F]/', $returnTo)
        ) {
            return redirect($returnTo);
        }

        return redirect()->route('artworks.show', [
            'slug' => 'uploaded-'.$artwork->id,
        ]);
    }
}
