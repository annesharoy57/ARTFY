<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_art_lover_can_like_and_unlike_artwork(): void
    {
        [$artist, $artwork] = $this->createPublicArtwork();
        $lover = $this->createUserWithRole('art_lover');
        $likeUrl = route('artworks.like', ['artwork' => $artwork->id]);
        $artworkUrl = route('artworks.show', [
            'slug' => 'uploaded-' . $artwork->id,
        ]);

        $this->actingAs($lover)->post($likeUrl)->assertRedirect($artworkUrl);
        $this->actingAs($lover)->post($likeUrl)->assertRedirect($artworkUrl);

        $this->assertDatabaseCount('artwork_likes', 1);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame(
            'artwork_liked',
            $artist->notifications()->firstOrFail()->type
        );

        $this->get($artworkUrl)
            ->assertOk()
            ->assertSee('1 like')
            ->assertSee('Liked');

        $this->actingAs($lover)
            ->delete($likeUrl)
            ->assertRedirect($artworkUrl);

        $this->assertDatabaseCount('artwork_likes', 0);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_art_lover_comment_is_public_and_notifies_artist(): void
    {
        [$artist, $artwork] = $this->createPublicArtwork();
        $lover = $this->createUserWithRole('art_lover');
        $artworkUrl = route('artworks.show', [
            'slug' => 'uploaded-' . $artwork->id,
        ]);

        $this->actingAs($lover)
            ->post(route('artworks.comments.store', [
                'artwork' => $artwork->id,
            ]), [
                'body' => 'A wonderful piece.',
            ])
            ->assertRedirect($artworkUrl);

        $this->assertDatabaseHas('artwork_comments', [
            'artwork_id' => $artwork->id,
            'user_id' => $lover->id,
            'body' => 'A wonderful piece.',
        ]);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame(
            'artwork_commented',
            $artist->notifications()->firstOrFail()->type
        );

        auth()->logout();

        $this->get($artworkUrl)
            ->assertOk()
            ->assertSee('A wonderful piece.')
            ->assertSee($lover->name);
    }

    public function test_only_art_lovers_can_like_or_comment(): void
    {
        [, $artwork] = $this->createPublicArtwork();
        $artistUser = $this->createUserWithRole('artist');

        $this->actingAs($artistUser)
            ->post(route('artworks.like', ['artwork' => $artwork->id]))
            ->assertForbidden();

        $this->actingAs($artistUser)
            ->post(route('artworks.comments.store', [
                'artwork' => $artwork->id,
            ]), [
                'body' => 'A comment',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('artwork_likes', 0);
        $this->assertDatabaseCount('artwork_comments', 0);
        $this->assertDatabaseCount('notifications', 0);
    }

    /**
     * @return array{User, Artwork}
     */
    private function createPublicArtwork(): array
    {
        $artist = $this->createUserWithRole('artist');

        $artwork = Artwork::create([
            'user_id' => $artist->id,
            'title' => 'Test artwork',
            'description' => 'A test artwork.',
            'image' => 'artworks/test.jpg',
            'status' => 'available',
            'moderation_status' => 'approved',
        ]);

        return [$artist, $artwork];
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }
}
