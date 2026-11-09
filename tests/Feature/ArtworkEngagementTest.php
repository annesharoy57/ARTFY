<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\ArtworkComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_art_lovers_can_like_and_save_an_artwork_and_remove_both(): void
    {
        [$artist, $artwork] = $this->approvedArtwork();
        $lover = $this->artLover();

        $this->actingAs($lover)
            ->post(route('artworks.like', $artwork))
            ->assertRedirect(route('artworks.show', 'uploaded-'.$artwork->id));
        $this->actingAs($lover)
            ->post(route('artworks.favorite', $artwork))
            ->assertRedirect(route('artworks.show', 'uploaded-'.$artwork->id));

        $this->assertDatabaseHas('artwork_likes', [
            'artwork_id' => $artwork->id,
            'user_id' => $lover->id,
        ]);
        $this->assertDatabaseHas('artwork_favorites', [
            'artwork_id' => $artwork->id,
            'user_id' => $lover->id,
        ]);
        $this->assertDatabaseCount('notifications', 2);

        $this->actingAs($lover)
            ->get(route('favorites.index'))
            ->assertOk()
            ->assertSee($artwork->title)
            ->assertSee('Remove from favorites');
        $this->actingAs($lover)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Remove from favorites');
        $this->actingAs($lover)
            ->get(route('artworks.index'))
            ->assertOk()
            ->assertSee('Remove from favorites');

        $this->actingAs($lover)
            ->post(route('artworks.like', $artwork))
            ->assertRedirect();
        $this->actingAs($lover)
            ->post(route('artworks.favorite', $artwork))
            ->assertRedirect();

        $this->assertDatabaseMissing('artwork_likes', [
            'artwork_id' => $artwork->id,
            'user_id' => $lover->id,
        ]);
        $this->assertDatabaseMissing('artwork_favorites', [
            'artwork_id' => $artwork->id,
            'user_id' => $lover->id,
        ]);
        $this->assertDatabaseCount('notifications', 2);
        $this->assertSame(2, $artist->notifications()->count());
    }

    public function test_art_lovers_can_post_and_edit_their_own_comments(): void
    {
        [$artist, $artwork] = $this->approvedArtwork();
        $lover = $this->artLover();
        $otherLover = $this->artLover();

        $this->actingAs($lover)
            ->post(route('artworks.comments.store', $artwork), [
                'body' => 'The colors are wonderful.',
            ])
            ->assertRedirect();

        $comment = ArtworkComment::firstOrFail();
        $this->assertSame($lover->id, $comment->user_id);
        $this->assertSame(1, $artist->notifications()->count());

        $this->actingAs($lover)
            ->patch(route('artworks.comments.update', [$artwork, $comment]), [
                'body' => 'The colors and composition are wonderful.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('artwork_comments', [
            'id' => $comment->id,
            'body' => 'The colors and composition are wonderful.',
        ]);

        $this->actingAs($otherLover)
            ->patch(route('artworks.comments.update', [$artwork, $comment]), [
                'body' => 'Changed by another user.',
            ])
            ->assertNotFound();

        $this->actingAs($artist)
            ->get(route('artworks.show', 'uploaded-'.$artwork->id))
            ->assertOk()
            ->assertSee($lover->name)
            ->assertSee($comment->fresh()->body)
            ->assertSee('People who liked this artwork')
            ->assertSee($lover->name);
    }

    public function test_artist_can_reply_and_like_a_comment_and_the_art_lover_is_notified(): void
    {
        [$artist, $artwork] = $this->approvedArtwork();
        $lover = $this->artLover();
        $comment = $artwork->comments()->create([
            'user_id' => $lover->id,
            'body' => 'I love this piece.',
        ]);

        $this->actingAs($lover)
            ->post(route('artworks.like', $artwork))
            ->assertRedirect();

        $this->actingAs($artist)
            ->post(route('artworks.comments.reply', [$artwork, $comment]), [
                'body' => 'Thank you for your kind words!',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('artwork_comments', [
            'artwork_id' => $artwork->id,
            'user_id' => $artist->id,
            'parent_id' => $comment->id,
            'body' => 'Thank you for your kind words!',
        ]);

        $this->actingAs($artist)
            ->post(route('artworks.comments.like', [$artwork, $comment]))
            ->assertRedirect();

        $this->assertDatabaseHas('artwork_comment_likes', [
            'artwork_comment_id' => $comment->id,
            'user_id' => $artist->id,
        ]);
        $this->assertSame(2, $lover->notifications()->count());
        $this->assertEqualsCanonicalizing(
            [
                'The artist replied to your comment',
                'The artist liked your comment',
            ],
            $lover->notifications()
                ->get()
                ->map(fn ($notification) => $notification->data['title'])
                ->all()
        );
    }

    public function test_only_art_lovers_can_like_and_save_artworks(): void
    {
        [, $artwork] = $this->approvedArtwork();
        $artist = $this->artistAccount();

        $this->actingAs($artist)
            ->post(route('artworks.like', $artwork))
            ->assertForbidden();
        $this->actingAs($artist)
            ->post(route('artworks.favorite', $artwork))
            ->assertForbidden();

        $this->assertDatabaseCount('artwork_likes', 0);
        $this->assertDatabaseCount('artwork_favorites', 0);
        $this->assertDatabaseCount('notifications', 0);
    }

    private function artLover(): User
    {
        $lover = User::factory()->create();
        $lover->role = 'art_lover';
        $lover->save();

        return $lover;
    }

    private function artistAccount(): User
    {
        $artist = User::factory()->create();
        $artist->role = 'artist';
        $artist->membership_status = 'active';
        $artist->save();
        $artist->artistApplication()->create([
            'artist_name' => $artist->name,
            'biography' => 'Artist biography',
            'art_category' => 'Painting',
            'status' => 'approved',
            'payment_status' => 'verified',
        ]);

        return $artist;
    }

    /**
     * @return array{0: User, 1: Artwork}
     */
    private function approvedArtwork(): array
    {
        $artist = $this->artistAccount();
        $artwork = $artist->artworks()->create([
            'title' => 'Quiet Morning',
            'description' => 'A study in warm light.',
            'image' => 'quiet-morning.jpg',
            'category' => 'Painting',
            'medium' => 'Oil',
            'price' => '2500.00',
            'status' => 'available',
            'moderation_status' => 'approved',
        ]);

        return [$artist, $artwork];
    }
}
