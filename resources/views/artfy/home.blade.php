@extends('artfy.layout')

@section('title', 'Discover original art')

@section('content')
    <section class="home-hero">
        <div class="home-hero-copy">
            <span class="eyebrow"><span class="eyebrow-dot"></span> A gallery without walls</span>
            <h1>Art for the way<br>you <em>feel.</em></h1>
            <p>Find original work, meet the artists behind it, and bring a little more meaning into your everyday.</p>
            <form class="home-search" action="{{ route('artworks.index') }}" method="GET">
                <span aria-hidden="true">⌕</span>
                <label class="visually-hidden" for="gallery-search">Search art and artists</label>
                <input id="gallery-search" type="search" name="q" placeholder="Search art, artists, and more">
                <button type="submit" aria-label="Search">→</button>
            </form>
            <div class="hero-actions">
                <a href="{{ route('artworks.index') }}" class="button button-dark">Explore artworks <span>↗</span></a>
                <a href="{{ route('artists.index') }}" class="hero-text-link">Meet the artists <span>→</span></a>
            </div>
            <div class="hero-social-proof"><span class="mini-avatars"><i>M</i><i>L</i><i>A</i></span><span>A community made for art lovers</span></div>
        </div>

        <a class="hero-feature" href="{{ route('artworks.show', 'quiet-morning') }}" aria-label="Discover Quiet Morning by Maya Chen">
            <div class="hero-feature-art artwork-art peach"><span class="hero-art-overline">Editor's pick <span>01 / 04</span></span><span class="feature-orbit"></span><span class="feature-sun"></span><span class="feature-hill"></span><span class="feature-leaf"></span></div>
            <div class="hero-feature-caption"><div><span class="small-title">Featured artwork</span><h2>Quiet Morning</h2><p>Original painting · Maya Chen</p></div><span class="feature-open">↗</span></div>
        </a>
        <span class="hero-decoration hero-decoration-one">✳</span>
        <span class="hero-decoration hero-decoration-two">✳</span>
    </section>

    <section class="home-categories">
        <div class="home-section-heading">
            <div><span class="small-title">Follow your curiosity</span><h2>Explore by medium</h2></div>
            <a class="subtle-link" href="{{ route('artworks.index') }}">View all artworks <span>→</span></a>
        </div>
        <div class="medium-list">
            @foreach ([
                ['name' => 'Painting', 'icon' => '◒', 'tone' => 'medium-peach'],
                ['name' => 'Photography', 'icon' => '▧', 'tone' => 'medium-blue'],
                ['name' => 'Illustration', 'icon' => '✳', 'tone' => 'medium-green'],
                ['name' => 'Digital', 'icon' => '⌘', 'tone' => 'medium-lilac'],
            ] as $medium)
                <a class="medium-item" href="{{ route('artworks.index', ['category' => $medium['name']]) }}">
                    <span class="medium-icon {{ $medium['tone'] }}">{{ $medium['icon'] }}</span>
                    <span>{{ $medium['name'] }}</span>
                    <span class="medium-arrow">↗</span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="home-artworks">
        <div class="home-section-heading">
            <div><span class="small-title">Thoughtfully selected</span><h2>Artworks to fall for</h2></div>
            <a class="subtle-link" href="{{ route('artworks.index', ['sort' => 'popular']) }}">Discover the collection <span>→</span></a>
        </div>
        <div class="home-artwork-grid">
            @foreach ($artworks as $index => $artwork)
                <a class="home-artwork-card" href="{{ route('artworks.show', $artwork['slug']) }}">
                    <div class="home-artwork-image artwork-art {{ $artwork['color'] }} artwork-composition-{{ $index + 1 }}">
                        <span class="artwork-type">{{ $artwork['category'] }}</span>
                        <span class="artwork-favorite" aria-label="{{ $artwork['likes'] }} likes">♡ <span>{{ $artwork['likes'] }}</span></span>
                        <span class="composition-shape"></span><span class="composition-shape-two"></span>
                        <span class="artwork-quick-view">Discover artwork <span>↗</span></span>
                    </div>
                    <div class="home-artwork-meta"><div><h3>{{ $artwork['title'] }}</h3><p>by <span>{{ $artwork['artist'] }}</span></p></div><strong>${{ $artwork['price'] }}</strong></div>
                </a>
            @endforeach
        </div>
    </section>

    <section class="artist-feature">
        <div class="artist-feature-visual artwork-art peach"><span class="artist-art-initial">M</span><span class="artist-visual-note">Making the everyday<br>feel extraordinary.</span></div>
        <div class="artist-feature-copy"><span class="small-title">Get to know the maker</span><h2>Meet Maya Chen<span>.</span></h2><p>Painter, observer, and collector of small moments. Maya’s warm, color-rich work finds beauty in the everyday.</p><a class="button button-dark" href="{{ route('artists.show', 'maya-chen') }}">Explore Maya’s profile <span>→</span></a><a class="artist-feature-all" href="{{ route('artists.index') }}">Discover all artists <span>↗</span></a></div>
    </section>

    <section class="home-exhibition">
        <div class="home-section-heading"><div><span class="small-title">Curated perspectives</span><h2>Come see it differently</h2></div><a class="subtle-link" href="{{ route('exhibitions.index') }}">All exhibitions <span>→</span></a></div>
        <a class="exhibition-feature artwork-art green" href="{{ route('exhibitions.show', $exhibitions[0]['slug']) }}">
            <span class="exhibition-feature-art"></span>
            <span class="exhibition-feature-info"><span class="exhibition-label">Now showing · Online exhibition</span><strong>{{ $exhibitions[0]['title'] }}</strong><span>{{ $exhibitions[0]['subtitle'] }}</span></span>
            <span class="exhibition-feature-arrow">↗</span>
        </a>
    </section>

    <section class="home-join">
        <span class="small-title">A little more art in your life</span>
        <h2>Find something that<br><em>feels like you.</em></h2>
        <p>Explore a growing collection of original work from independent artists.</p>
        <a class="button button-dark" href="{{ route('artworks.index') }}">Find your next favorite <span>→</span></a>
    </section>
@endsection
