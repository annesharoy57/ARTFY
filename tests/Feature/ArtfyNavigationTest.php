<?php

namespace Tests\Feature;

use Tests\TestCase;

class ArtfyNavigationTest extends TestCase
{
    public function test_main_artfy_pages_are_reachable(): void
    {
        foreach ([
            '/',
            '/artworks',
            '/artists',
            '/exhibitions',
            '/favorites',
            '/artist/studio',
            '/admin',
            '/login',
            '/register',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_home_navigation_and_featured_links_point_to_artfy_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('>Home</a>', false)
            ->assertSee('>Artworks</a>', false)
            ->assertSee('>Artists</a>', false)
            ->assertSee('>Exhibitions</a>', false)
            ->assertSee('>Login</a>', false)
            ->assertSee('>Sign Up</a>', false)
            ->assertSee('href="'.route('artworks.index').'"', false)
            ->assertSee('href="'.route('artists.index').'"', false);
    }

    public function test_navigation_marks_the_current_gallery_page_active(): void
    {
        $this->get('/artists')
            ->assertOk()
            ->assertSee('<a class="active" href="'.route('artists.index').'">Artists</a>', false);
    }

    public function test_artfy_brand_has_its_custom_mark_and_wordmark(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>Artfy</title>', false)
            ->assertSee('rel="icon" type="image/svg+xml" href="'.asset('artfy-logo.svg').'?v=1"', false)
            ->assertSee('rel="shortcut icon" href="'.asset('artfy-logo.svg').'?v=1"', false)
            ->assertSee('class="brand-mark" aria-hidden="true"', false)
            ->assertSee('<span class="brand-art">Art</span><span class="brand-fy">fy</span><span class="brand-dot">.</span>', false)
            ->assertSee('artfy.css?v=20261014-artfy-brand-2', false);
    }

    public function test_browser_title_matches_each_gallery_section(): void
    {
        $this->get('/artworks')->assertOk()->assertSee('<title>Artworks</title>', false);
        $this->get('/artworks/quiet-morning')->assertOk()->assertSee('<title>Artworks</title>', false);
        $this->get('/artists')->assertOk()->assertSee('<title>Artists</title>', false);
        $this->get('/exhibitions')->assertOk()->assertSee('<title>Exhibitions</title>', false);
    }

    public function test_homepage_shows_art_discovery_sections_and_routes(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Art for the way')
            ->assertSee('Artworks to fall for')
            ->assertSee('Follow your curiosity')
            ->assertSee('Meet Maya Chen')
            ->assertSee('Come see it differently')
            ->assertSee('Find your next favorite')
            ->assertSee('href="'.route('artworks.index').'"', false)
            ->assertSee('href="'.route('artists.index').'"', false)
            ->assertSee('href="'.route('exhibitions.show', 'color-in-motion').'"', false)
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('href="'.route('register').'"', false);
    }

    public function test_artwork_artist_and_exhibition_cards_open_detail_pages(): void
    {
        $this->get('/artworks/quiet-morning')->assertOk()->assertSee('Quiet Morning');
        $this->get('/artists/maya-chen')->assertOk()->assertSee('Maya Chen');
        $this->get('/exhibitions/color-in-motion')->assertOk()->assertSee('Color in Motion');
    }

    public function test_artwork_search_and_filters_are_applied(): void
    {
        $this->get('/artworks?q=Quiet')->assertOk()->assertSee('Quiet Morning')->assertDontSee('Blue Hour');
        $this->get('/artworks?category=Photography')->assertOk()->assertSee('Blue Hour')->assertDontSee('Quiet Morning');
    }

    public function test_unknown_gallery_details_return_not_found(): void
    {
        $this->get('/artworks/not-a-real-artwork')->assertNotFound();
        $this->get('/artists/not-a-real-artist')->assertNotFound();
        $this->get('/exhibitions/not-a-real-exhibition')->assertNotFound();
    }
}
