<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtistFollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_art_lover_can_follow_and_unfollow_an_artist(): void
    {
        $artist = $this->createApprovedArtist();
        $lover = User::factory()->create();
        $lover->role = 'art_lover';
        $lover->save();
        $artistProfileUrl = route('artists.show', [
            'slug' => 'artist-' . $artist->id,
        ]);

        $this->actingAs($lover)
            ->post(route('artists.follow', ['artist' => $artist->id]))
            ->assertRedirect($artistProfileUrl);

        $this->assertDatabaseHas('artist_follows', [
            'follower_id' => $lover->id,
            'artist_id' => $artist->id,
        ]);
        $this->assertDatabaseCount('notifications', 1);

        $notification = $artist->notifications()->firstOrFail();
        $this->assertSame('You have a new follower', $notification->data['title']);
        $this->assertSame(
            route('artists.show', [
                'slug' => 'artist-' . $artist->id,
            ], false),
            $notification->data['url']
        );
        $this->assertStringContainsString(
            $lover->name,
            $notification->data['message']
        );

        $this->actingAs($lover)
            ->get($artistProfileUrl)
            ->assertOk()
            ->assertSee('<strong>1</strong>', false)
            ->assertSee('Followers')
            ->assertSee('Unfollow');

        $this->actingAs($lover)
            ->delete(route('artists.unfollow', ['artist' => $artist->id]))
            ->assertRedirect($artistProfileUrl);

        $this->assertDatabaseMissing('artist_follows', [
            'follower_id' => $lover->id,
            'artist_id' => $artist->id,
        ]);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_repeated_follow_does_not_duplicate_follow_or_notification(): void
    {
        $artist = $this->createApprovedArtist();
        $lover = User::factory()->create();
        $lover->role = 'art_lover';
        $lover->save();
        $followUrl = route('artists.follow', ['artist' => $artist->id]);

        $this->actingAs($lover)->post($followUrl)->assertRedirect();
        $this->actingAs($lover)->post($followUrl)->assertRedirect();

        $this->assertDatabaseCount('artist_follows', 1);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_only_art_lovers_can_follow_artists(): void
    {
        $artist = $this->createApprovedArtist();
        $anotherArtist = $this->createApprovedArtist();

        $this->actingAs($anotherArtist)
            ->post(route('artists.follow', ['artist' => $artist->id]))
            ->assertForbidden();

        $this->actingAs($artist)
            ->post(route('artists.follow', ['artist' => $artist->id]))
            ->assertForbidden();

        $this->assertDatabaseCount('artist_follows', 0);
        $this->assertDatabaseCount('notifications', 0);
    }

    private function createApprovedArtist(): User
    {
        $artist = User::factory()->create();
        $artist->role = 'artist';
        $artist->save();

        $artist->artistApplication()->create([
            'artist_name' => $artist->name,
            'biography' => 'Artist biography',
            'art_category' => 'Painting',
            'status' => 'approved',
            'payment_status' => 'verified',
        ]);

        $artist->artistProfile()->create([
            'artist_name' => $artist->name,
        ]);

        return $artist;
    }
}
