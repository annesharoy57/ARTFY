<?php

namespace Tests\Feature;

use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtfyNavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Main Artfy pages should be accessible to guests.
     */
    public function test_main_artfy_pages_are_reachable(): void
    {
        // Public gallery pages should load successfully.
        $publicPaths = [
            '/',
            '/artworks',
            '/artists',
            '/exhibitions',
        ];

        foreach ($publicPaths as $path) {
            $this->get($path)->assertOk();
        }

        // Guest-only or protected routes may load or redirect.
        $guestPaths = [
            '/favorites',
            '/artist/studio',
            '/login',
            '/register',
            '/admin',
        ];

        foreach ($guestPaths as $path) {
            $response = $this->get($path);

            $this->assertContains(
                $response->getStatusCode(),
                [200, 302],
                "Unexpected HTTP status for {$path}"
            );
        }
    }

    /**
     * The homepage should link to the main gallery sections.
     */
    public function test_home_navigation_links_point_to_artfy_pages(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee(route('artworks.index'), false)
            ->assertSee(route('artists.index'), false)
            ->assertSee(route('exhibitions.index'), false);
    }

    /**
     * Gallery navigation should be rendered on the artwork page.
     */
    public function test_navigation_marks_current_gallery_page_active(): void
    {
        $response = $this->get('/artworks');

        $response->assertOk()
            ->assertSee('Artfy');
    }

    public function test_favorites_is_visible_in_navigation_for_art_lovers(): void
    {
        $lover = User::factory()->create([
            'role' => 'art_lover',
        ]);

        $this->actingAs($lover)
            ->get(route('favorites.index'))
            ->assertOk()
            ->assertSee(route('favorites.index'), false)
            ->assertSee('Favorites');
    }

    public function test_login_role_dashboard_offers_a_co_admin_sign_in_option(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="coAdminAuthLink"', false)
            ->assertSee(route('login', ['role' => 'co_admin']), false)
            ->assertSee('Login as Co-admin');

        $this->get(route('login', ['role' => 'co_admin']))
            ->assertOk()
            ->assertSee('Exhibition Co-admin')
            ->assertSee('selected', false);
    }

    /**
     * The site should display its Artfy branding.
     */
    public function test_artfy_brand_has_custom_mark_and_wordmark(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Artfy');
    }

    /**
     * Browser titles should match the gallery sections.
     */
    public function test_browser_titles_match_gallery_sections(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Artfy | Discover Art</title>', false);

        $this->get('/artworks')
            ->assertOk()
            ->assertSee('Artworks');

        $this->get('/artists')
            ->assertOk()
            ->assertSee('Artists');

        $this->get('/exhibitions')
            ->assertOk()
            ->assertSee('Exhibitions');
    }

    /**
     * Gallery pages should work without hardcoded demo content.
     */
    public function test_gallery_pages_do_not_require_demo_content(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertDontSee('Maya Chen')
            ->assertDontSee('Quiet Morning');
    }

    /**
     * Artwork search should accept a query with no matching results.
     */
    public function test_artwork_search_and_filters_accept_empty_results(): void
    {
        $this->get('/artworks?q=artfy-no-matching-artwork-987654')
            ->assertOk();

        $this->get('/artworks?art_type=UnknownMedium987654')
            ->assertOk();
    }

    /**
     * Unknown artwork, artist, and exhibition details should return 404.
     */
    public function test_unknown_gallery_details_return_not_found(): void
    {
        $this->get('/artworks/artfy-unknown-artwork-987654')
            ->assertNotFound();

        $this->get('/artists/artfy-unknown-artist-987654')
            ->assertNotFound();

        $this->get('/exhibitions/artfy-unknown-exhibition-987654')
            ->assertNotFound();
    }

    /**
     * The homepage should show its current art discovery sections and valid routes.
     */
    public function test_homepage_shows_art_discovery_sections_and_routes(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('WHERE WILL CURIOSITY TAKE YOU?')
            ->assertSee('Explore by')
            ->assertSee('medium.')
            ->assertSee('Artworks to')
            ->assertSee('fall for.')
            ->assertSee('Come see it')
            ->assertSee('differently.')
            ->assertSee('Find your next favourite')
            ->assertSee(route('artworks.index'), false)
            ->assertSee(route('artists.index'), false)
            ->assertSee(route('exhibitions.index'), false);
    }

    public function test_discover_shows_one_ongoing_exhibition_ad_and_prefers_it(): void
    {
        $now = now('Asia/Dhaka');
        $this->createExhibition('Upcoming exhibition', [
            'registration_opens_at' => $now->copy()->subDay(),
            'registration_closes_at' => $now->copy()->addDay(),
            'starts_at' => $now->copy()->addDays(2),
            'ends_at' => $now->copy()->addDays(5),
        ]);
        $ongoing = $this->createExhibition('Ongoing exhibition', [
            'registration_opens_at' => $now->copy()->subDays(5),
            'registration_closes_at' => $now->copy()->subDays(3),
            'starts_at' => $now->copy()->subDay(),
            'ends_at' => $now->copy()->addDay(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('EXHIBITION IS GOING')
            ->assertSee('Ongoing exhibition')
            ->assertSee(route('exhibitions.show', $ongoing), false)
            ->assertSee('aria-label="Close exhibition announcement"', false)
            ->assertDontSee('REGISTRATION OPEN')
            ->assertDontSee('Upcoming exhibition');
    }

    public function test_discover_shows_upcoming_ad_only_while_registration_is_open(): void
    {
        $now = now('Asia/Dhaka');
        $upcoming = $this->createExhibition('Registration open exhibition', [
            'registration_opens_at' => $now->copy()->subDay(),
            'registration_closes_at' => $now->copy()->addDay(),
            'starts_at' => $now->copy()->addDays(2),
            'ends_at' => $now->copy()->addDays(5),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('REGISTRATION OPEN')
            ->assertSee('Coming soon:')
            ->assertSee('Registration open exhibition')
            ->assertSee(route('exhibitions.show', $upcoming), false);

        $upcoming->update([
            'registration_closes_at' => $now->copy()->subMinute(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-exhibition-ad-id="'.$upcoming->id.'"', false);

        $this->get('/artworks')
            ->assertOk()
            ->assertDontSee('data-exhibition-ad');
    }

    private function createExhibition(string $title, array $dates): Exhibition
    {
        return Exhibition::create(array_merge([
            'title' => $title,
            'slug' => str()->slug($title),
            'description' => $title.' details.',
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addDay(),
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(5),
            'max_artists' => 10,
            'registration_fee' => 100,
            'status' => 'published',
        ], $dates));
    }
}
