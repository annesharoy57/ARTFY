@extends('artfy.layout')

@section('title', 'Discover original art')

@section('content')

    {{-- PERSONALIZED WELCOME --}}
    @auth
        @if (auth()->user()->isArtist() || auth()->user()->isArtLover())
            <section class="container" style="padding-top: 28px; padding-bottom: 0;">
                <div style="background: #eeeee4; border: 1px solid rgba(85, 97, 58, 0.12); border-radius: 14px; padding: 20px 26px;">
                    <span style="display: block; margin-bottom: 6px; color: #72795f; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;">
                        Your Artfy space
                    </span>

                    <h2 style="margin: 0 0 6px; color: #414c2c; font-family: 'Playfair Display', serif; font-size: clamp(23px, 3vw, 30px); font-weight: 600;">
                        Welcome back, {{ auth()->user()->name }}!
                    </h2>

                    <p style="margin: 0; color: #72795f; font-size: 14px; line-height: 1.7;">
                        @if (auth()->user()->isArtist())
                            Your creative journey continues here. Discover inspiration and share your art with the world.
                        @else
                            A little inspiration goes a long way. Discover art, meet artists, and find something you love.
                        @endif
                    </p>
                </div>
            </section>
        @endif
    @endauth

    {{-- HERO: INDEPENDENT CURATED IMAGE --}}
    <section class="home-hero">
        <div class="home-hero-copy">
            <span class="eyebrow">
                <span class="eyebrow-dot"></span>
                A gallery without walls
            </span>

            <h1>
                Art for the way<br>
                you <em>feel.</em>
            </h1>

            <p>
                Find original work, meet the artists behind it, and bring
                a little more meaning into your everyday.
            </p>

            <form class="home-search" action="{{ route('artworks.index') }}" method="GET">
                <span aria-hidden="true">⌕</span>

                <label class="visually-hidden" for="gallery-search">
                    Search art and artists
                </label>

                <input
                    id="gallery-search"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search art, artists, and more"
                >

                <button type="submit" aria-label="Search">→</button>
            </form>

            <div class="hero-actions">
                <a href="{{ route('artworks.index') }}" class="button button-dark">
                    Explore artworks <span>↗</span>
                </a>

                <a href="{{ route('artists.index') }}" class="hero-text-link">
                    Meet the artists <span>→</span>
                </a>
            </div>

            <div class="hero-social-proof">
                <span class="small-title">Created for curious minds</span>
            </div>
        </div>

        <div class="hero-feature hero-feature-curated">
            <div class="hero-feature-art hero-curated-art">
                <img
                    class="hero-curated-image"
                    src="{{ asset('images/artfy-hero.jpg') }}"
                    alt="A curated artwork selected for the Artfy gallery"
                    loading="eager"
                    fetchpriority="high"
                >
            </div>

            <div class="hero-feature-caption">
                <div>
                    <span class="small-title">A little inspiration</span>
                    <h2>Almond Blossom (1890)</h2>
                    <p>Vincent van Gogh</p>
                </div>

                <span class="feature-open" aria-hidden="true">✳</span>
            </div>
        </div>

        <span class="hero-decoration hero-decoration-one">✳</span>
        <span class="hero-decoration hero-decoration-two">✳</span>
    </section>

    {{-- EXPLORE BY MEDIUM --}}
    <section class="home-categories">
        <div class="home-section-heading">
            <div>
                <span class="small-title">Follow your curiosity</span>
                <h2>Explore by medium</h2>
            </div>

            <a class="subtle-link" href="{{ route('artworks.index') }}">
                View all artworks <span>→</span>
            </a>
        </div>

        <div class="medium-list">
            @foreach ([
                ['name' => 'Painting', 'icon' => '◒', 'tone' => 'medium-peach'],
                ['name' => 'Photography', 'icon' => '▧', 'tone' => 'medium-blue'],
                ['name' => 'Illustration', 'icon' => '✳', 'tone' => 'medium-green'],
                ['name' => 'Digital', 'icon' => '⌘', 'tone' => 'medium-green'],
            ] as $medium)
                <a
                    class="medium-item"
                    href="{{ route('artworks.index', ['art_type' => $medium['name']]) }}"
                >
                    <span class="medium-icon {{ $medium['tone'] }}">
                        {{ $medium['icon'] }}
                    </span>

                    <span>{{ $medium['name'] }}</span>
                    <span class="medium-arrow">↗</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- FEATURED ARTWORKS --}}
    <section class="home-artworks">
        <div class="home-section-heading">
            <div>
                <span class="small-title">Thoughtfully selected</span>
                <h2>Artworks to fall for</h2>
            </div>

            <a
                class="subtle-link"
                href="{{ route('artworks.index', ['sort' => 'popular']) }}"
            >
                Discover the collection <span>→</span>
            </a>
        </div>

        <div class="home-artwork-grid">
            @forelse ($artworks as $index => $artwork)
                <a
                    class="home-artwork-card"
                    href="{{ route('artworks.show', $artwork['slug']) }}"
                >
                    <div class="home-artwork-image artwork-art {{ $artwork['color'] ?? 'green' }} artwork-composition-{{ ($index % 3) + 1 }}">
                        @if (!empty($artwork['image']))
                            <img
                                class="home-artwork-photo"
                                src="{{ asset('storage/' . ltrim($artwork['image'], '/')) }}"
                                alt="{{ $artwork['title'] }}"
                                loading="lazy"
                            >
                        @else
                            <span class="composition-shape"></span>
                            <span class="composition-shape-two"></span>
                        @endif

                        <span class="artwork-type">
                            {{ $artwork['category'] ?? 'Artwork' }}
                        </span>

                        <span
                            class="artwork-favorite"
                            aria-label="{{ $artwork['likes'] ?? 0 }} likes"
                        >
                            ♡ <span>{{ $artwork['likes'] ?? 0 }}</span>
                        </span>

                        <span class="artwork-quick-view">
                            Discover artwork <span>↗</span>
                        </span>
                    </div>

                    <div class="home-artwork-meta">
                        <div>
                            <h3>{{ $artwork['title'] }}</h3>
                            <p>
                                by <span>{{ $artwork['artist'] ?? 'Artfy artist' }}</span>
                            </p>
                        </div>

                        <strong>
                            ৳{{ number_format((float) ($artwork['price'] ?? 0), 2) }}
                        </strong>
                    </div>
                </a>
            @empty
                <div
                    class="gallery-empty-state"
                    style="grid-column: 1 / -1; padding: 35px 24px; text-align: center; border: 1px solid rgba(85, 97, 58, 0.15); border-radius: 14px; background: #f5f3e8;"
                >
                    <h3 style="color: #414c2c; margin-bottom: 8px;">
                        Your next favourite artwork is yet to arrive.
                    </h3>

                    <p style="color: #72795f; margin-bottom: 18px;">
                        Approved artworks uploaded by artists will appear here.
                    </p>

                    <a href="{{ route('artworks.index') }}" class="button button-dark">
                        Explore artworks <span>↗</span>
                    </a>
                </div>
            @endforelse
        </div>
    </section>

    {{-- FEATURED ARTIST --}}
    @if (!empty($artists) && isset($artists[0]))
        @php
            $featuredArtist = $artists[0];
        @endphp

        <section class="artist-feature">
            <div class="artist-feature-visual artwork-art green">
                @if (!empty($featuredArtist['image']))
                    <img
                        class="artist-feature-photo"
                        src="{{ asset('storage/' . ltrim($featuredArtist['image'], '/')) }}"
                        alt="{{ $featuredArtist['name'] }}"
                        loading="lazy"
                    >
                @else
                    <span class="artist-art-initial">
                        {{ mb_strtoupper(mb_substr($featuredArtist['name'], 0, 1)) }}
                    </span>

                    <span class="artist-visual-note">
                        Making the everyday<br>
                        feel extraordinary.
                    </span>
                @endif
            </div>

            <div class="artist-feature-copy">
                <span class="small-title">Get to know the maker</span>

                <h2>
                    Meet {{ $featuredArtist['name'] }}<span>.</span>
                </h2>

                <p>
                    {{ $featuredArtist['bio'] ?? 'Discover the story and creative work of this artist.' }}
                </p>

                <a
                    class="button button-dark"
                    href="{{ route('artists.show', $featuredArtist['slug']) }}"
                >
                    Explore artist profile <span>→</span>
                </a>

                <a class="artist-feature-all" href="{{ route('artists.index') }}">
                    Discover all artists <span>↗</span>
                </a>
            </div>
        </section>
    @endif

    {{-- EXHIBITIONS --}}
    <section class="home-exhibition">
        <div class="home-section-heading">
            <div>
                <span class="small-title">Curated perspectives</span>
                <h2>Come see it differently</h2>
            </div>

            <a class="subtle-link" href="{{ route('exhibitions.index') }}">
                All exhibitions <span>→</span>
            </a>
        </div>

        @if (!empty($exhibitions) && isset($exhibitions[0]))
            <a
                class="exhibition-feature artwork-art green"
                href="{{ route('exhibitions.show', $exhibitions[0]['slug']) }}"
            >
                <span class="exhibition-feature-art"></span>

                <span class="exhibition-feature-info">
                    <span class="exhibition-label">Online exhibition</span>
                    <strong>{{ $exhibitions[0]['title'] }}</strong>
                    <span>{{ $exhibitions[0]['subtitle'] ?? '' }}</span>
                </span>

                <span class="exhibition-feature-arrow">↗</span>
            </a>
        @else
            <div
                style="padding: 24px; border: 1px solid rgba(85, 97, 58, 0.15); border-radius: 14px; color: #72795f; background: #f5f3e8;"
            >
                New exhibitions will appear here when they become available.
            </div>
        @endif
    </section>

    {{-- JOIN ARTFY --}}
    <section class="home-join">
        <span class="small-title">A little more art in your life</span>

        <h2>
            Find something that<br>
            <em>feels like you.</em>
        </h2>

        <p>
            Explore a growing collection of original work from independent artists.
        </p>

        <a class="button button-dark" href="{{ route('artworks.index') }}">
            Find your next favourite <span>→</span>
        </a>
    </section>

@endsection
