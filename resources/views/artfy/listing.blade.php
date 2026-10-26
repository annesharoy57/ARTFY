@extends('artfy.layout')

@section('title', $title)

@section('content')
@php
    $resolveArtfyImage = function ($image) {
        if (empty($image)) {
            return null;
        }

        $image = trim((string) $image);

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        if (str_starts_with($image, 'storage/')) {
            return asset($image);
        }

        return asset('storage/' . ltrim($image, '/'));
    };

    $featuredArtworks = collect($items)
        ->filter(fn ($item) => !empty($item['image']))
        ->values();
@endphp

<style>
    .art-gallery {
        --cream: #f5f3e8;
        --olive: #55613a;
        --dark: #303a25;
        --muted: #72795f;
        --line: #e4e3d7;
        --paper: #fffef9;
        color: var(--dark);
    }

    .art-gallery *,
    .art-gallery *::before,
    .art-gallery *::after {
        box-sizing: border-box;
    }

    .art-gallery a {
        -webkit-tap-highlight-color: transparent;
    }

    /* SHARED HERO */

    .ag-hero {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 420px;
        overflow: hidden;
        isolation: isolate;
        padding: clamp(28px, 5vw, 65px);
        border-radius: 18px;
        background: var(--dark);
        color: var(--cream);
    }

    .ag-hero-background {
        position: absolute;
        z-index: -3;
        inset: 0;
        overflow: hidden;
        background: var(--dark);
    }

    .ag-background-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        background-position: center;
        background-size: cover;
        transform: scale(1.04);
        transition: opacity 1.2s ease, transform 5s ease;
    }

    .ag-background-slide.is-active {
        opacity: 1;
        transform: scale(1);
    }

    .ag-hero::before {
        content: "";
        position: absolute;
        z-index: -2;
        inset: 0;
        background: linear-gradient(
            90deg,
            rgba(38, 48, 28, .91),
            rgba(48, 60, 35, .78) 43%,
            rgba(65, 78, 45, .48)
        );
        pointer-events: none;
    }

    .ag-hero::after {
        content: "";
        position: absolute;
        z-index: -1;
        inset: 0;
        border: 1px solid rgba(245, 243, 232, .15);
        border-radius: inherit;
        pointer-events: none;
    }

    .ag-hero-copy {
        position: relative;
        z-index: 2;
        width: min(100%, 650px);
    }

    .ag-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        color: #d4ddbd;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .19em;
        text-transform: uppercase;
    }

    .ag-eyebrow::before {
        content: "";
        width: 25px;
        height: 1px;
        background: currentColor;
    }

    .ag-hero h1 {
        margin: 0;
        color: var(--cream);
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(43px, 6vw, 76px);
        font-weight: 400;
        letter-spacing: -.045em;
        line-height: 1.04;
    }

    .ag-hero h1 em {
        color: #d2ddb6;
        font-weight: 400;
    }

    .ag-hero-copy > p {
        max-width: 490px;
        margin: 22px 0 27px;
        color: rgba(255, 254, 249, .88);
        font-size: 14px;
        line-height: 1.9;
    }

    .ag-hero-button {
        display: inline-flex;
        align-items: center;
        gap: 13px;
        padding: 13px 18px;
        border: 1px solid rgba(255, 255, 255, .6);
        border-radius: 5px;
        background: var(--cream);
        color: var(--dark);
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        transition: background .2s, transform .2s;
    }

    .ag-hero-button:hover {
        transform: translateY(-2px);
        background: white;
        color: var(--dark);
    }

    .ag-hero-bottom {
        position: absolute;
        z-index: 3;
        right: 28px;
        bottom: 22px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ag-hero-indicators {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ag-hero-dot {
        width: 7px;
        height: 7px;
        padding: 0;
        border: 0;
        border-radius: 50px;
        background: rgba(255, 255, 255, .5);
        cursor: pointer;
        transition: width .25s, background .25s;
    }

    .ag-hero-dot.is-active {
        width: 23px;
        background: var(--cream);
    }

    .ag-hero-number {
        color: white;
        font-size: 10px;
        letter-spacing: .12em;
    }

    .ag-hero-empty {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse at 80% 25%, rgba(205, 218, 175, .24), transparent 45%),
            linear-gradient(125deg, #303a25, #55613a);
    }


/* ARTISTS HERO: STATIC PHOTO BACKGROUND */
.ag-artist-hero {
    min-height: 330px;
    margin-bottom: 30px;
    background-color: #303a25;
    background-image:
        linear-gradient(
            90deg,
            rgba(35, 44, 27, .88) 0%,
            rgba(48, 58, 37, .72) 52%,
            rgba(48, 58, 37, .38) 100%
        ),
        url('https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=1800&q=85');
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
}

.ag-artist-hero::before {
    background: rgba(38, 48, 28, .12);
}

.ag-artist-hero .ag-hero-copy {
    width: min(100%, 620px);
}

.ag-artist-hero .ag-hero-copy > p {
    max-width: 460px;
}

.ag-artist-hero-decoration {
    display: none;
}

.ag-artist-hero-decoration::before,
.ag-artist-hero-decoration::after {
    content: "";
    position: absolute;
    border: 1px solid rgba(245, 243, 232, .19);
    border-radius: 50%;
}

.ag-artist-hero-decoration::before {
    inset: 25px;
}

.ag-artist-hero-decoration::after {
    inset: 55px;
}
    /* ARTWORK FILTERS */

    .ag-filters {
        margin-top: 24px;
        padding: 18px;
        border: 1px solid var(--line);
        border-radius: 13px;
        background: var(--paper);
    }

    .ag-filter-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 13px;
    }

    .ag-filter-heading strong {
        font-family: Georgia, serif;
        font-size: 18px;
        font-weight: 500;
    }

    .ag-filter-heading span {
        color: var(--muted);
        font-size: 11px;
    }

    .ag-filter-grid {
        display: grid;
        grid-template-columns: minmax(170px, 2fr) repeat(4, minmax(100px, 1fr));
        gap: 9px;
    }

    .ag-filter-grid input,
    .ag-filter-grid select {
        width: 100%;
        min-width: 0;
        height: 42px;
        padding: 0 11px;
        border: 1px solid var(--line);
        border-radius: 7px;
        outline: none;
        background: #fffefb;
        color: var(--dark);
        font: inherit;
        font-size: 11px;
    }

    .ag-filter-grid input:focus,
    .ag-filter-grid select:focus {
        border-color: #89966e;
        box-shadow: 0 0 0 3px rgba(137, 150, 110, .13);
    }

    .ag-filter-grid select {
        cursor: pointer;
    }

    .ag-search {
        position: relative;
    }

    .ag-search .ag-search-icon {
        position: absolute;
        top: 50%;
        left: 11px;
        color: var(--muted);
        font-size: 18px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .ag-search input {
        padding-left: 33px;
    }

    .ag-filter-bottom {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 12px;
        color: var(--muted);
        font-size: 10px;
    }

    .ag-filter-bottom a {
        color: var(--olive);
        font-weight: 700;
        text-decoration: none;
    }

    .art-gallery .visually-hidden {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    /* COLLECTION HEADINGS */

    .ag-collection {
        margin-top: 39px;
        scroll-margin-top: 25px;
    }

    .ag-collection-heading {
        display: flex;
        justify-content: space-between;
        align-items: end;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 18px;
    }

    .ag-collection-heading p {
        margin: 0 0 6px;
        color: var(--muted);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .ag-collection-heading h2 {
        margin: 0;
        color: var(--dark);
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(27px, 3vw, 36px);
        font-weight: 400;
        letter-spacing: -.025em;
    }

    .ag-count {
        padding: 7px 12px;
        border: 1px solid var(--line);
        border-radius: 30px;
        background: var(--paper);
        color: var(--muted);
        font-size: 11px;
    }

    /* ARTWORK CARDS */

    .ag-artwork-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .art-gallery .ag-art-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow: hidden;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: var(--paper);
        color: inherit;
        text-decoration: none;
        transition: transform .22s, box-shadow .22s, border-color .22s;
    }

    .art-gallery .ag-art-card:hover {
        transform: translateY(-3px);
        border-color: #c7ceb7;
        box-shadow: 0 10px 22px rgba(48, 58, 37, .09);
    }

    .ag-art-image {
        position: relative;
        width: 100%;
        height: 195px;
        min-height: 195px;
        overflow: hidden;
        background: #f0eee5;
    }

    .ag-art-image img {
        display: block;
        width: 100%;
        height: 100%;
        padding: 7px;
        object-fit: contain;
        object-position: center;
        background: #f0eee5;
        transition: transform .3s;
    }

    .ag-art-card:hover .ag-art-image img {
        transform: scale(1.025);
    }

    .ag-category {
        position: absolute;
        top: 8px;
        left: 8px;
        max-width: calc(100% - 16px);
        overflow: hidden;
        padding: 5px 8px;
        border-radius: 30px;
        background: rgba(255, 254, 249, .94);
        color: var(--dark);
        font-size: 9px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ag-art-meta {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 0;
        padding: 12px;
    }

    .ag-art-meta h3 {
        display: -webkit-box;
        overflow: hidden;
        margin: 0;
        color: var(--dark);
        font-family: Georgia, "Times New Roman", serif;
        font-size: 15px;
        font-weight: 500;
        line-height: 1.4;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .ag-art-artist {
        overflow: hidden;
        margin-top: 5px;
        color: var(--muted);
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ag-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 5px;
        margin-top: auto;
        padding-top: 11px;
    }

    .ag-price {
        overflow-wrap: anywhere;
        color: var(--olive);
        font-size: 11px;
        font-weight: 800;
    }

    .ag-view {
        color: var(--muted);
        font-size: 10px;
        white-space: nowrap;
    }

    .ag-no-image {
        display: grid;
        place-items: center;
        width: 100%;
        height: 100%;
        padding: 15px;
        background: #f0eee5;
        color: #777966;
        font-size: 11px;
        text-align: center;
    }

/* ARTIST DIRECTORY */
.ag-artist-intro {
    display: flex;
    align-items: end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 22px;
}

.ag-artist-intro h2 {
    margin: 0;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(29px, 3vw, 40px);
    font-weight: 400;
    letter-spacing: -.035em;
}

.ag-artist-intro p {
    max-width: 510px;
    margin: 9px 0 0;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.8;
}

/* Compact artist grid */
.ag-artist-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    align-items: stretch;
}

.art-gallery .ag-artist-card {
    display: flex;
    flex-direction: column;
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--line);
    border-radius: 10px;
    background: var(--paper);
    color: inherit;
    text-decoration: none;
    transition: transform .22s, box-shadow .22s, border-color .22s;
}

.art-gallery .ag-artist-card:hover {
    transform: translateY(-3px);
    border-color: #c7ceb7;
    box-shadow: 0 10px 22px rgba(48, 58, 37, .09);
}

.ag-artist-photo {
    position: relative;
    display: grid;
    place-items: center;
    width: 100%;
    height: 175px;
    overflow: hidden;
    background:
        radial-gradient(
            circle at 80% 20%,
            rgba(210, 221, 182, .4),
            transparent 40%
        ),
        var(--cream);
}

.ag-artist-photo::after {
    content: "";
    position: absolute;
    inset: 8px;
    border: 1px solid rgba(85, 97, 58, .12);
    pointer-events: none;
}

.ag-artist-photo img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform .3s;
}

.ag-artist-card:hover .ag-artist-photo img {
    transform: scale(1.035);
}

.ag-avatar-fallback {
    display: grid;
    place-items: center;
    width: 72px;
    height: 72px;
    border: 1px solid rgba(85, 97, 58, .25);
    border-radius: 50%;
    background: #e8eadc;
    color: var(--olive);
    font-family: Georgia, "Times New Roman", serif;
    font-size: 32px;
}

.ag-artist-card-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
    padding: 13px;
}

.ag-artist-label {
    margin-bottom: 5px;
    color: var(--olive);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.ag-artist-name {
    margin: 0;
    color: var(--dark);
    font-family: Georgia, "Times New Roman", serif;
    font-size: 19px;
    font-weight: 400;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.ag-artist-discipline {
    display: inline-block;
    max-width: 100%;
    margin-top: 7px;
    padding: 5px 8px;
    border: 1px solid var(--line);
    border-radius: 30px;
    background: var(--cream);
    color: var(--olive);
    font-size: 10px;
    overflow-wrap: anywhere;
}

.ag-artist-bio {
    display: -webkit-box;
    overflow: hidden;
    margin: 9px 0 11px;
    color: var(--muted);
    font-size: 11px;
    line-height: 1.65;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
}

.ag-artist-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 10px;
    margin-top: auto;
    padding-top: 10px;
    border-top: 1px solid var(--line);
    color: var(--dark);
    font-size: 11px;
    font-weight: 800;
}

.ag-artist-link span:last-child {
    color: var(--olive);
    font-size: 16px;
    transition: transform .2s;
}

.ag-artist-card:hover .ag-artist-link span:last-child {
    transform: translate(3px, -3px);
}


    /* EXHIBITIONS */

    .ag-exhibition-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
    }

    .ag-exhibition-card {
        display: block;
        min-height: 190px;
        padding: 20px;
        border: 1px solid var(--line);
        border-radius: 11px;
        background: var(--cream);
        color: var(--dark);
        text-decoration: none;
    }

    .ag-exhibition-card strong {
        display: block;
        margin-top: 55px;
        font-family: Georgia, serif;
        font-size: 23px;
        font-weight: 400;
    }

    .ag-exhibition-card small {
        display: block;
        margin-top: 8px;
        color: var(--muted);
    }

    /* EMPTY STATE */

    .ag-empty {
        padding: 45px 20px;
        border: 1px dashed #cdd2bf;
        border-radius: 14px;
        background: var(--paper);
        text-align: center;
    }

    .ag-empty-symbol {
        color: var(--olive);
        font-size: 28px;
    }

    .ag-empty h2 {
        margin: 8px 0;
        font-family: Georgia, serif;
        font-size: 27px;
        font-weight: 400;
    }

    .ag-empty p {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.8;
    }

    .ag-empty a {
        display: inline-block;
        margin-top: 8px;
        padding: 10px 15px;
        border-radius: 5px;
        background: var(--dark);
        color: white;
        font-size: 12px;
        text-decoration: none;
    }

    /* RESPONSIVE */

    @media (max-width: 1050px) {
        .ag-artist-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .ag-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .ag-search {
            grid-column: span 2;
        }

        .ag-artist-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .ag-hero {
            min-height: 420px;
            padding: 35px 24px 65px;
        }

        .ag-hero h1 {
            font-size: clamp(42px, 9vw, 58px);
        }

        .ag-hero-copy > p {
            font-size: 13px;
        }

        .ag-hero-bottom {
            right: 20px;
            bottom: 18px;
        }

        .ag-artist-hero {
            min-height: 350px;
        }

        .ag-artist-hero-decoration {
            right: -110px;
            opacity: .6;
        }

        .ag-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ag-search {
            grid-column: 1 / -1;
        }

        .ag-artwork-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 11px;
        }

        .ag-art-image {
            height: 170px;
            min-height: 170px;
        }

        .ag-art-meta {
            padding: 10px;
        }

        .ag-art-meta h3 {
            font-size: 14px;
        }

       .ag-artist-grid,
.ag-exhibition-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.ag-artist-photo {
    height: 155px;
}

.ag-artist-card-body {
    padding: 12px;
}

.ag-artist-name {
    font-size: 18px;
}

.ag-artist-bio {
    font-size: 11px;
}
    }

    @media (max-width: 430px) {
        .ag-artist-grid {
            grid-template-columns: 1fr;
        }

        .ag-artist-photo {
            aspect-ratio: 5 / 3;
        }

        .ag-artist-card-body {
            padding: 17px;
        }
    }

    @media (max-width: 380px) {
        .ag-hero {
            padding-right: 18px;
            padding-left: 18px;
        }

        .ag-artwork-grid {
            gap: 8px;
        }

        .ag-art-image {
            height: 145px;
            min-height: 145px;
        }

        .ag-art-meta {
            padding: 8px;
        }

        .ag-view {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .art-gallery *,
        .art-gallery *::before,
        .art-gallery *::after {
            scroll-behavior: auto !important;
            animation: none !important;
            transition: none !important;
        }
    }
</style>

<div class="art-gallery">

    {{-- HERO --}}
    @if ($pageType === 'artists')
        <section class="ag-hero ag-artist-hero">
            <div class="ag-artist-hero-decoration" aria-hidden="true"></div>

            <div class="ag-hero-copy">
                <span class="ag-eyebrow">The people behind the art</span>

                <h1>Meet the minds<br>behind <em>the art.</em></h1>

                <p>
                    Every artist has a story, a perspective, and a unique way
                    of seeing the world. Get to know the creatives in our
                    community and explore the people behind their work.
                </p>

                <a href="#listing-results" class="ag-hero-button">
                    Meet our artists <span aria-hidden="true">↘</span>
                </a>
            </div>
        </section>
    @else
        <section class="ag-hero">
            @if ($pageType === 'artworks')
                <div class="ag-hero-background" id="agHeroBackground">
                    @forelse ($featuredArtworks as $index => $item)
                        @php
                            $backgroundImage = $resolveArtfyImage($item['image'] ?? null);
                        @endphp

                        <div
                            class="ag-background-slide {{ $index === 0 ? 'is-active' : '' }}"
                            data-hero-slide="{{ $index }}"
                            style="background-image: url('{{ $backgroundImage }}');"
                            role="img"
                            aria-label="{{ $item['title'] ?? 'Featured artwork' }}"
                        ></div>
                    @empty
                        <div class="ag-hero-empty"></div>
                    @endforelse
                </div>
            @endif

            <div class="ag-hero-copy">
                <span class="ag-eyebrow">
                    {{ $pageType === 'artworks' ? 'The Artfy collection' : $eyebrow }}
                </span>

                @if ($pageType === 'artworks')
                    <h1>Art with<br><em>depth &amp; soul.</em></h1>

                    <p>
                        Explore original artwork uploaded by independent artists.
                        Discover different perspectives, find your favourite pieces,
                        and bring creativity closer to you.
                    </p>

                    <a href="#all-artworks" class="ag-hero-button">
                        Explore all artworks <span aria-hidden="true">↘</span>
                    </a>
                @else
                    <h1>{{ $title }}</h1>
                    <p>{{ $description }}</p>
                @endif
            </div>

            @if ($pageType === 'artworks' && $featuredArtworks->count() > 1)
                <div class="ag-hero-bottom" aria-label="Artwork slideshow controls">
                    <span class="ag-hero-number" id="agHeroNumber">
                        01 / {{ str_pad($featuredArtworks->count(), 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <div class="ag-hero-indicators">
                        @foreach ($featuredArtworks as $index => $item)
                            <button
                                type="button"
                                class="ag-hero-dot {{ $index === 0 ? 'is-active' : '' }}"
                                data-hero-dot="{{ $index }}"
                                aria-label="Show artwork {{ $index + 1 }}"
                                aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                            ></button>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif

    {{-- ARTWORK FILTERS: ONLY SHOWN ON ARTWORKS PAGE --}}
    @if ($pageType === 'artworks')
        <section class="ag-filters" aria-label="Search and filter artworks">
            <div class="ag-filter-heading">
                <strong>Find your next favourite</strong>
                <span>Search · Refine · Discover</span>
            </div>

            <form
                id="artworkFilters"
                class="ag-filter-grid"
                action="{{ route('artworks.index') }}"
                method="GET"
            >
                <label class="ag-search">
                    <span class="ag-search-icon" aria-hidden="true">⌕</span>
                    <span class="visually-hidden">Search artworks or artists</span>
                    <input
                        type="search"
                        name="q"
                        value="{{ $query ?? '' }}"
                        placeholder="Search art or artist..."
                    >
                </label>

                <label>
                    <span class="visually-hidden">Artwork format</span>
                    <select name="artwork_type" aria-label="Artwork format">
                        <option value="">All formats</option>
                        <option value="physical" @selected(($artworkFormat ?? '') === 'physical')>Physical</option>
                        <option value="digital" @selected(($artworkFormat ?? '') === 'digital')>Digital</option>
                    </select>
                </label>

                <label>
                    <span class="visually-hidden">Artistic medium</span>
                    <select name="medium" aria-label="Artistic medium">
                        <option value="">All mediums</option>
                        @foreach ([
                            'oil painting' => 'Oil painting',
                            'watercolor painting' => 'Watercolor',
                            'pastel painting' => 'Pastel',
                            'acrylic painting' => 'Acrylic',
                            'ink wash painting' => 'Ink wash',
                            'encaustic painting' => 'Encaustic',
                            'spray painting' => 'Spray painting',
                            'sketching' => 'Sketching',
                            'photography' => 'Photography',
                            'digital painting' => 'Digital painting',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(($mediumFilter ?? '') === $value)
                            >{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="visually-hidden">Maximum budget</span>
                    <select name="max_price" aria-label="Maximum budget">
                        <option value="">Any budget</option>
                        @foreach ([2000, 3000, 5000, 10000, 20000] as $amount)
                            <option
                                value="{{ $amount }}"
                                @selected((string) ($maxPrice ?? '') === (string) $amount)
                            >
                                Up to ৳{{ number_format($amount) }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="visually-hidden">Sort artworks</span>
                    <select name="sort" aria-label="Sort artworks">
                        <option value="latest" @selected(($sort ?? 'latest') === 'latest')>Newest first</option>
                        <option value="oldest" @selected(($sort ?? '') === 'oldest')>Oldest first</option>
                        <option value="price-low" @selected(($sort ?? '') === 'price-low')>Price: low to high</option>
                        <option value="price-high" @selected(($sort ?? '') === 'price-high')>Price: high to low</option>
                        <option value="popular" @selected(($sort ?? '') === 'popular')>Most popular</option>
                    </select>
                </label>
            </form>

            <div class="ag-filter-bottom">
                <span>Discover art that fits your taste and budget.</span>
                <a href="{{ route('artworks.index') }}">Clear filters ↗</a>
            </div>
        </section>
    @endif

    {{-- LISTING RESULTS --}}
    <section
        class="ag-collection"
        id="{{ $pageType === 'artworks' ? 'all-artworks' : 'listing-results' }}"
    >
        @if ($pageType === 'artworks')
            <div class="ag-collection-heading">
                <div>
                    <p>Collected with creativity</p>
                    <h2>All artworks</h2>
                </div>

                <span class="ag-count">
                    {{ count($items) }}
                    {{ count($items) === 1 ? 'artwork' : 'artworks' }}
                </span>
            </div>
        @elseif ($pageType === 'artists')
            <div class="ag-artist-intro">
                <div>
                    <h2>Our creative community</h2>
                    <p>
                        Discover the artists, learn about their creative interests,
                        and visit their profiles to explore their portfolios.
                    </p>
                </div>

                <span class="ag-count">
                    {{ count($items) }}
                    {{ count($items) === 1 ? 'artist' : 'artists' }}
                </span>
            </div>
        @endif

        @if (count($items) === 0)
            <div class="ag-empty">
                <div class="ag-empty-symbol" aria-hidden="true">✳</div>

                <h2>
                    @if ($pageType === 'artworks')
                        No artworks found
                    @elseif ($pageType === 'artists')
                        Our community is growing
                    @else
                        Nothing here just yet
                    @endif
                </h2>

                <p>
                    @if ($pageType === 'artworks')
                        Try another search or clear your filters to see the full collection.
                    @elseif ($pageType === 'artists')
                        There are no artists to display right now. Check back as the Artfy community grows.
                    @else
                        Check back as the Artfy community grows.
                    @endif
                </p>

                @if ($pageType === 'artworks')
                    <a href="{{ route('artworks.index') }}">See all artworks</a>
                @endif
            </div>

        @elseif ($pageType === 'artworks')
            <div class="ag-artwork-grid">
                @foreach ($items as $item)
                    @php
                        $cardImage = $resolveArtfyImage($item['image'] ?? null);
                    @endphp

                    <a
                        class="ag-art-card"
                        href="{{ route('artworks.show', $item['slug']) }}"
                    >
                        <div class="ag-art-image">
                            @if ($cardImage)
                                <img
                                    src="{{ $cardImage }}"
                                    alt="{{ $item['title'] ?? 'Artwork' }}"
                                    loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.hidden=false;"
                                >
                                <div class="ag-no-image" hidden>
                                    Image could not be loaded.
                                </div>
                            @else
                                <div class="ag-no-image">
                                    No artwork image available
                                </div>
                            @endif

                            <span class="ag-category">
                                {{ $item['category'] ?? 'Other' }}
                            </span>
                        </div>

                        <div class="ag-art-meta">
                            <h3>{{ $item['title'] ?? 'Untitled artwork' }}</h3>

                            <div class="ag-art-artist">
                                by {{ $item['artist'] ?? 'Artfy Artist' }}
                            </div>

                            <div class="ag-card-footer">
                                <span class="ag-price">
                                    @if (isset($item['price']) && $item['price'] !== null)
                                        ৳{{ number_format((float) $item['price'], 2) }}
                                    @else
                                        Price not listed
                                    @endif
                                </span>

                                <span class="ag-view">View ↗</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

        @elseif ($pageType === 'artists')
            <div class="ag-artist-grid">
                @foreach ($items as $item)
                    @php
                        /*
                         * GalleryController supplies the artist's profile_photo
                         * through the array key "image".
                         */
                        $artistImage = $resolveArtfyImage($item['image'] ?? null);
                        $artistName = $item['name'] ?? 'Artfy Artist';
                        $artistInitial = mb_strtoupper(
                            mb_substr($artistName, 0, 1)
                        );
                    @endphp

                    <a
                        class="ag-artist-card"
                        href="{{ route('artists.show', $item['slug']) }}"
                    >
                        <div class="ag-artist-photo">
                            @if ($artistImage)
                                <img
                                    src="{{ $artistImage }}"
                                    alt="Portrait of {{ $artistName }}"
                                    loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.hidden=false;"
                                >
                                <div
                                    class="ag-avatar-fallback"
                                    hidden
                                    aria-hidden="true"
                                >{{ $artistInitial }}</div>
                            @else
                                <div
                                    class="ag-avatar-fallback"
                                    aria-hidden="true"
                                >{{ $artistInitial }}</div>
                            @endif
                        </div>

                        <div class="ag-artist-card-body">
                            <span class="ag-artist-label">Artfy artist</span>

                            <h3 class="ag-artist-name">
                                {{ $artistName }}
                            </h3>

                            <span class="ag-artist-discipline">
                                {{ $item['discipline'] ?? 'Independent artist' }}
                            </span>

                            <p class="ag-artist-bio">
                                {{ $item['bio'] ?? 'Discover original artwork and the creative journey of this independent artist.' }}
                            </p>

                            <div class="ag-artist-link">
                                <span>View artist profile</span>
                                <span aria-hidden="true">↗</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

        @else
            <div class="ag-exhibition-grid">
                @foreach ($items as $item)
                    <a
                        class="ag-exhibition-card artwork-art {{ $item['color'] ?? 'green' }}"
                        href="{{ route('exhibitions.show', $item['slug']) }}"
                    >
                        <span>Online exhibition</span>
                        <strong>{{ $item['title'] }}</strong>
                        <small>{{ $item['subtitle'] ?? '' }}</small>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>

@if ($pageType === 'artworks')
<script>
document.addEventListener('DOMContentLoaded', function () {
    /* Automatically submit artwork filters */
    const filters = document.getElementById('artworkFilters');

    if (filters) {
        filters.querySelectorAll('select').forEach(function (select) {
            select.addEventListener('change', function () {
                filters.requestSubmit();
            });
        });

        const search = filters.querySelector('input[name="q"]');

        if (search) {
            search.addEventListener('search', function () {
                filters.requestSubmit();
            });

            search.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    filters.requestSubmit();
                }
            });
        }
    }

    /* Artwork hero slideshow */
    const background = document.getElementById('agHeroBackground');

    if (!background) return;

    const slides = Array.from(
        background.querySelectorAll('.ag-background-slide')
    );

    const dots = Array.from(
        document.querySelectorAll('.ag-hero-dot')
    );

    const numberLabel = document.getElementById('agHeroNumber');

    if (slides.length < 2) return;

    let activeIndex = 0;
    let rotationTimer = null;

    const pad = function (number) {
        return String(number).padStart(2, '0');
    };

    function showSlide(index) {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach(function (slide, position) {
            slide.classList.toggle('is-active', position === activeIndex);
        });

        dots.forEach(function (dot, position) {
            const active = position === activeIndex;

            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (numberLabel) {
            numberLabel.textContent =
                pad(activeIndex + 1) + ' / ' + pad(slides.length);
        }
    }

    function stopRotation() {
        if (rotationTimer) {
            clearInterval(rotationTimer);
            rotationTimer = null;
        }
    }

    function startRotation() {
        stopRotation();

        if (slides.length < 2 || document.hidden) return;

        rotationTimer = setInterval(function () {
            showSlide(activeIndex + 1);
        }, 4000);
    }

    dots.forEach(function (dot, index) {
        dot.addEventListener('click', function () {
            showSlide(index);
            startRotation();
        });
    });

    const hero = document.querySelector('.ag-hero');

    if (hero) {
        hero.addEventListener('mouseenter', stopRotation);
        hero.addEventListener('mouseleave', startRotation);
        hero.addEventListener('focusin', stopRotation);
        hero.addEventListener('focusout', startRotation);
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopRotation();
        } else {
            startRotation();
        }
    });

    showSlide(0);
    startRotation();
});
</script>
@endif
@endsection
