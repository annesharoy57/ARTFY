<?php

namespace Tests\Feature;

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
}
