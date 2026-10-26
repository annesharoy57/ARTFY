@extends('artfy.layout')

@section('title', 'Discover original art')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/artfy-home.css') }}?v=2">
@endpush

@section('content')
<div class="artfy-discover-page">

    {{-- PERSONALIZED WELCOME --}}
    @auth
        @if (auth()->user()->isArtist() || auth()->user()->isArtLover())
            <section class="discover-welcome container">
                <div class="discover-welcome-card">
                    <span class="discover-eyebrow">YOUR ARTFY SPACE</span>
                    <h2>Welcome back, {{ auth()->user()->name }}.</h2>
                    <p>
                        @if (auth()->user()->isArtist())
                            Your creative journey continues here. Find inspiration
                            and share your art with the world.
                        @else
                            Take a moment to discover something beautiful.
                            Explore art and meet the artists behind it.
                        @endif
                    </p>
                </div>
            </section>
        @endif
    @endauth

    {{-- EDITORIAL HERO --}}
    <section class="editorial-hero">
        <div class="editorial-hero-copy">
            <span class="editorial-kicker">
                <span class="editorial-kicker-line"></span>
                AN ONLINE GALLERY FOR THE CURIOUS
            </span>

            <h1>
                Art that<br>
                <em>stays with you.</em>
            </h1>

            <p class="editorial-hero-description">
                Original work. Distinctive perspectives. Artists with
                stories worth discovering. Find the piece that speaks to you.
            </p>

            <div class="editorial-hero-actions">
                <a href="{{ route('artworks.index') }}" class="editorial-button">
                    Explore the collection <span>↗</span>
                </a>

                <a href="{{ route('artists.index') }}" class="editorial-secondary-link">
                    Meet our artists <span>→</span>
                </a>
            </div>

            <div class="editorial-hero-note">
                <span class="editorial-note-mark">✳</span>
                <span>For spaces, stories, and the things we feel.</span>
            </div>
        </div>

        <div class="editorial-hero-visual">
            <div class="editorial-image-frame">
                <img
                    src="{{ asset('images/artfy-hero.jpg') }}"
                    alt="Featured art selected for the Artfy gallery"
                    class="editorial-hero-image"
                    fetchpriority="high"
                >
                <span class="editorial-image-index">ARTFY / 001</span>
            </div>

            <div class="editorial-art-caption">
                <div>
                    <span class="editorial-caption-label">A MOMENT OF INSPIRATION</span>
                    <h2>Almond Blossom</h2>
                    <p>Vincent van Gogh · 1890</p>
                </div>
                <span class="editorial-caption-symbol">↗</span>
            </div>

            <span class="editorial-hero-orbit" aria-hidden="true"></span>
        </div>

        <div class="editorial-hero-bottom">
            <span>ART IS A WAY OF SEEING</span>
            <span>DISCOVER SOMETHING UNEXPECTED ↓</span>
        </div>
    </section>

    {{-- SEARCH --}}
    <section class="editorial-search-section">
        <div>
            <span class="editorial-kicker">WHERE WILL CURIOSITY TAKE YOU?</span>
            <h2>Find your next <em>favourite.</em></h2>
        </div>

        <form class="editorial-search"
              action="{{ route('artworks.index') }}"
              method="GET">
            <label class="visually-hidden" for="gallery-search">
                Search art and artists
            </label>

            <span aria-hidden="true">⌕</span>

            <input
                id="gallery-search"
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search artworks, artists, ideas..."
            >

            <button type="submit" aria-label="Search artworks">→</button>
        </form>
    </section>

    {{-- EXPLORE BY MEDIUM --}}
    <section class="editorial-section editorial-mediums">
        <div class="editorial-section-heading">
            <div>
                <span class="editorial-kicker">START WITH WHAT YOU LOVE</span>
                <h2>Explore by <em>medium.</em></h2>
            </div>

            <a class="editorial-text-link" href="{{ route('artworks.index') }}">
                View all artworks <span>↗</span>
            </a>
        </div>

        <div class="editorial-medium-grid">
            @foreach ([
                ['name' => 'Painting', 'number' => '01', 'description' => 'Colour, canvas & expression'],
                ['name' => 'Photography', 'number' => '02', 'description' => 'Life through another lens'],
                ['name' => 'Illustration', 'number' => '03', 'description' => 'Ideas brought to life'],
                ['name' => 'Digital', 'number' => '04', 'description' => 'Art beyond the expected'],
            ] as $medium)
                <a
                    class="editorial-medium-card"
                    href="{{ route('artworks.index', ['art_type' => $medium['name']]) }}"
                >
                    <span class="editorial-medium-number">{{ $medium['number'] }}</span>
                    <span class="editorial-medium-title">{{ $medium['name'] }}</span>
                    <span class="editorial-medium-description">{{ $medium['description'] }}</span>
                    <span class="editorial-medium-arrow">↗</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- FEATURED ARTWORKS: 3D CAROUSEL --}}
    <section class="editorial-carousel-section" id="artfy-collection">
        <div class="editorial-section-heading editorial-carousel-heading">
            <div>
                <span class="editorial-kicker">A COLLECTION THAT MOVES YOU</span>
                <h2>Artworks to <em>fall for.</em></h2>
                <p class="editorial-section-intro">
                    Take your time. There is a story in every piece.
                </p>
            </div>

            <a
                class="editorial-text-link"
                href="{{ route('artworks.index', ['sort' => 'popular']) }}"
            >
                Discover all artworks <span>↗</span>
            </a>
        </div>

        @if (count($artworks))
            <div class="artfy-carousel"
                 data-artfy-carousel
                 aria-label="Featured artworks carousel">

                <button
                    type="button"
                    class="artfy-carousel-arrow artfy-carousel-prev"
                    data-carousel-prev
                    aria-label="Previous artwork"
                >←</button>

                <div class="artfy-carousel-stage" data-carousel-stage>
                    @foreach ($artworks as $index => $artwork)
                        <a
                            class="artfy-carousel-card"
                            data-carousel-card
                            data-carousel-index="{{ $index }}"
                            href="{{ route('artworks.show', $artwork['slug']) }}"
                            aria-label="View {{ $artwork['title'] }}"
                            @if ($index > 0) tabindex="-1" @endif
                        >
                            <div class="artfy-carousel-image artwork-art {{ $artwork['color'] ?? 'green' }} artwork-composition-{{ ($index % 3) + 1 }}">
                                @if (!empty($artwork['image']))
                                    <img
                                        src="{{ asset('storage/' . ltrim($artwork['image'], '/')) }}"
                                        alt="{{ $artwork['title'] }}"
                                        loading="lazy"
                                    >
                                @else
                                    <span class="composition-shape"></span>
                                    <span class="composition-shape-two"></span>
                                @endif

                                <span class="artfy-carousel-category">
                                    {{ $artwork['category'] ?? 'Artwork' }}
                                </span>

                                <span class="artfy-carousel-open">VIEW ARTWORK ↗</span>
                            </div>

                            <div class="artfy-carousel-info">
                                <div>
                                    <span class="artfy-carousel-artist">
                                        {{ $artwork['artist'] ?? 'Artfy artist' }}
                                    </span>
                                    <h3>{{ $artwork['title'] }}</h3>
                                </div>

                                <strong>
                                    ৳{{ number_format((float) ($artwork['price'] ?? 0), 2) }}
                                </strong>
                            </div>
                        </a>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="artfy-carousel-arrow artfy-carousel-next"
                    data-carousel-next
                    aria-label="Next artwork"
                >→</button>
            </div>

            <div class="artfy-carousel-controls">
                <span class="artfy-carousel-counter">
                    <strong data-carousel-current>01</strong>
                    <span class="artfy-counter-divider"></span>
                    <span data-carousel-total>{{ str_pad(count($artworks), 2, '0', STR_PAD_LEFT) }}</span>
                </span>

                <div class="artfy-carousel-dots" data-carousel-dots>
                    @foreach ($artworks as $index => $artwork)
                        <button
                            type="button"
                            data-carousel-dot="{{ $index }}"
                            aria-label="Show artwork {{ $index + 1 }}"
                            @if ($index === 0) aria-current="true" @endif
                        ></button>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="artfy-carousel-autoplay"
                    data-carousel-toggle
                    aria-pressed="false"
                >
                    <span data-carousel-toggle-label>PAUSE MOTION</span>
                    <span aria-hidden="true">Ⅱ</span>
                </button>
            </div>
        @else
            <div class="editorial-empty-state">
                <span class="editorial-empty-symbol">✳</span>
                <h3>A collection is taking shape.</h3>
                <p>
                    Approved artworks uploaded by artists will appear here.
                </p>
                <a href="{{ route('artworks.index') }}" class="editorial-button">
                    Explore artworks <span>↗</span>
                </a>
            </div>
        @endif
    </section>

    {{-- FEATURED ARTIST --}}
    @if (!empty($artists) && isset($artists[0]))
        @php
            $featuredArtist = $artists[0];
        @endphp

        <section class="editorial-artist-section">
            <div class="editorial-artist-image">
                @if (!empty($featuredArtist['image']))
                    <img
                        src="{{ asset('storage/' . ltrim($featuredArtist['image'], '/')) }}"
                        alt="{{ $featuredArtist['name'] }}"
                        loading="lazy"
                    >
                @else
                    <div class="editorial-artist-placeholder">
                        <span>{{ mb_strtoupper(mb_substr($featuredArtist['name'], 0, 1)) }}</span>
                        <small>THE ARTIST BEHIND THE WORK</small>
                    </div>
                @endif
                <span class="editorial-image-index">ARTFY / ARTIST</span>
            </div>

            <div class="editorial-artist-copy">
                <span class="editorial-kicker">BEHIND THE CANVAS</span>
                <h2>Every artist has<br>a story. <em>Meet theirs.</em></h2>

                <p class="editorial-featured-name">
                    {{ $featuredArtist['name'] }}
                </p>

                <p class="editorial-artist-bio">
                    {{ $featuredArtist['bio'] ?? 'Discover the story and creative work of this artist.' }}
                </p>

                <a
                    href="{{ route('artists.show', $featuredArtist['slug']) }}"
                    class="editorial-button"
                >
                    Explore artist profile <span>↗</span>
                </a>

                <a class="editorial-text-link editorial-all-artists"
                   href="{{ route('artists.index') }}">
                    Discover all artists <span>→</span>
                </a>
            </div>
        </section>
    @endif

    {{-- EXHIBITIONS --}}
    <section class="editorial-section editorial-exhibitions">
        <div class="editorial-section-heading">
            <div>
                <span class="editorial-kicker">CURATED PERSPECTIVES</span>
                <h2>Come see it <em>differently.</em></h2>
            </div>

            <a class="editorial-text-link" href="{{ route('exhibitions.index') }}">
                All exhibitions <span>↗</span>
            </a>
        </div>

        @if (!empty($exhibitions) && isset($exhibitions[0]))
            <a
                class="editorial-exhibition-card artwork-art green"
                href="{{ route('exhibitions.show', $exhibitions[0]['slug']) }}"
            >
                <span class="editorial-exhibition-art"></span>

                <span class="editorial-exhibition-content">
                    <span class="editorial-kicker">ONLINE EXHIBITION</span>
                    <strong>{{ $exhibitions[0]['title'] }}</strong>
                    <span>{{ $exhibitions[0]['subtitle'] ?? '' }}</span>
                    <span class="editorial-exhibition-cta">ENTER EXHIBITION ↗</span>
                </span>

                <span class="editorial-exhibition-number">01</span>
            </a>
        @else
            <div class="editorial-empty-exhibition">
                New exhibitions will appear here when they become available.
            </div>
        @endif
    </section>

    {{-- CLOSING INVITATION --}}
    <section class="editorial-closing">
        <span class="editorial-kicker">MAKE ROOM FOR SOMETHING MEANINGFUL</span>
        <h2>Find the piece<br>that feels like <em>you.</em></h2>
        <p>
            Discover original work from independent artists
            and bring a different perspective into your world.
        </p>
        <a href="{{ route('artworks.index') }}" class="editorial-button editorial-button-light">
            Find your next favourite <span>↗</span>
        </a>
        <span class="editorial-closing-decoration" aria-hidden="true">✳</span>
    </section>

</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-artfy-carousel]').forEach(function (carousel) {
        if (carousel.dataset.initialized === 'true') return;
        carousel.dataset.initialized = 'true';

        const cards = Array.from(carousel.querySelectorAll('[data-carousel-card]'));
        const previous = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');
        const stage = carousel.querySelector('[data-carousel-stage]');
        const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
        const currentLabel = carousel.querySelector('[data-carousel-current]');
        const toggle = carousel.querySelector('[data-carousel-toggle]');
        const toggleLabel = carousel.querySelector('[data-carousel-toggle-label]');

        if (!cards.length || !stage) return;

        let active = 0;
        let autoplay = true;
        let timer = null;
        let startX = 0;

        const reducedMotion = window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches;

        if (reducedMotion) autoplay = false;

        function relativePosition(index) {
            let position = index - active;
            const length = cards.length;

            if (position > length / 2) position -= length;
            if (position < -length / 2) position += length;

            return position;
        }

        function render() {
            cards.forEach(function (card, index) {
                const position = relativePosition(index);
                const distance = Math.abs(position);

                // Center card + up to three cards on each side.
                const visible = distance <= 3;

                card.style.setProperty('--carousel-position', position);
                card.style.setProperty('--carousel-distance', distance);
                card.style.zIndex = String(30 - distance);

                card.style.opacity = visible
                    ? String(distance === 0 ? 1 : Math.max(0.48, 1 - distance * 0.15))
                    : '0';

                card.style.pointerEvents = visible ? 'auto' : 'none';
                card.setAttribute('aria-hidden', visible ? 'false' : 'true');
                card.tabIndex = position === 0 ? 0 : -1;
                card.classList.toggle('is-active', position === 0);
            });

            dots.forEach(function (dot, index) {
                if (index === active) {
                    dot.setAttribute('aria-current', 'true');
                } else {
                    dot.removeAttribute('aria-current');
                }
            });

            if (currentLabel) {
                currentLabel.textContent = String(active + 1).padStart(2, '0');
            }
        }

        function goTo(index) {
            active = (index + cards.length) % cards.length;
            render();
        }

        function stopTimer() {
            if (timer !== null) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        function startTimer() {
            stopTimer();

            if (!autoplay || reducedMotion || cards.length < 2) return;

            timer = window.setInterval(function () {
                goTo(active + 1);
            }, 4200);
        }

        function updateToggle() {
            if (!toggle || !toggleLabel) return;

            toggle.setAttribute('aria-pressed', autoplay ? 'false' : 'true');
            toggleLabel.textContent = autoplay ? 'PAUSE MOTION' : 'RESUME MOTION';

            const icon = toggle.querySelector('[aria-hidden="true"]');
            if (icon) icon.textContent = autoplay ? 'Ⅱ' : '▶';
        }

        if (previous) {
            previous.addEventListener('click', function () {
                goTo(active - 1);
                startTimer();
            });
        }

        if (next) {
            next.addEventListener('click', function () {
                goTo(active + 1);
                startTimer();
            });
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                goTo(Number(dot.dataset.carouselDot));
                startTimer();
            });
        });

        if (toggle) {
            toggle.addEventListener('click', function () {
                autoplay = !autoplay;
                updateToggle();
                startTimer();
            });
        }

        carousel.addEventListener('mouseenter', stopTimer);
        carousel.addEventListener('mouseleave', startTimer);
        carousel.addEventListener('focusin', stopTimer);
        carousel.addEventListener('focusout', startTimer);

        carousel.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                goTo(active - 1);
                startTimer();
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                goTo(active + 1);
                startTimer();
            }
        });

        stage.addEventListener('touchstart', function (event) {
            startX = event.changedTouches[0].screenX;
        }, { passive: true });

        stage.addEventListener('touchend', function (event) {
            const difference = event.changedTouches[0].screenX - startX;

            if (Math.abs(difference) > 45) {
                goTo(active + (difference < 0 ? 1 : -1));
                startTimer();
            }
        }, { passive: true });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stopTimer();
            } else {
                startTimer();
            }
        });

        render();
        updateToggle();
        startTimer();
    });
});
</script>
@endpush
