
@extends('artfy.layout')

@section('title', $title)

@section('content')

<style>
    .page-intro {
        color: #424b35;
    }

    .page-intro .eyebrow {
        color: #77845c;
    }

    .page-intro h1 {
        color: #39442d;
    }

    .filter-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        width: 100%;
        margin-top: 26px;
    }

    .filter-bar .search-field {
        position: relative;
        display: flex;
        align-items: center;
        flex: 2 1 220px;
        min-width: 180px;
    }

    .filter-bar .search-field > span:first-child {
        display: none;
    }

    .filter-bar > label:not(.search-field) {
        flex: 1 1 150px;
        min-width: 0;
    }

    .filter-bar select,
    .filter-bar input {
        box-sizing: border-box;
        width: 100%;
        min-width: 0;
        height: 46px;
        padding: 0 12px;
        border: 1px solid #d9ddcd;
        border-radius: 10px;
        background: #fffef9;
        color: #414b32;
        font: inherit;
        font-size: 13px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .filter-bar select {
        cursor: pointer;
    }

    .filter-bar input::placeholder {
        color: #858a79;
    }

    .filter-bar input:focus,
    .filter-bar select:focus {
        outline: none;
        border-color: #89966e;
        box-shadow: 0 0 0 3px rgba(137, 150, 110, 0.16);
    }

    /* Artwork card visual container */
    .artfy-card-visual {
        position: relative;
        overflow: hidden;
        background-color: #e6e9d8;
    }

    /* Hide decorative shapes behind uploaded artwork images */
    .artfy-card-visual:has(.artfy-card-photo)::before,
    .artfy-card-visual:has(.artfy-card-photo)::after {
        content: none !important;
        display: none !important;
        background: none !important;
        background-image: none !important;
    }

    .artfy-card-visual:has(.artfy-card-photo) {
        background: #f8f6f2 !important;
        background-image: none !important;
        isolation: isolate;
    }

    /* Show the entire uploaded image without cropping */
    .artfy-card-photo {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        z-index: 0;
    }

    .artfy-card-visual .art-card-category,
    .artfy-card-visual .like-count {
        position: relative;
        z-index: 1;
    }

    /* Keep artist photos styled as before */
    .artfy-artist-photo {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: inherit;
    }

    .artist-avatar:has(.artfy-artist-photo)::before,
    .artist-avatar:has(.artfy-artist-photo)::after {
        content: none !important;
        display: none !important;
    }

    .art-card-info strong {
        color: #53613d;
    }

    .empty-state {
        color: #424b35;
    }

    @media (max-width: 900px) {
        .filter-bar {
            gap: 8px;
        }

        .filter-bar .search-field {
            flex: 1 1 220px;
        }

        .filter-bar > label:not(.search-field) {
            flex: 1 1 150px;
        }

        .filter-bar select,
        .filter-bar input {
            font-size: 12px;
            padding: 0 9px;
        }
    }

    @media (max-width: 650px) {
        .filter-bar {
            flex-wrap: wrap;
        }

        .filter-bar .search-field {
            flex: 1 1 100%;
        }

        .filter-bar > label:not(.search-field) {
            flex: 1 1 calc(50% - 8px);
        }
    }

    @media (max-width: 380px) {
        .filter-bar > label:not(.search-field) {
            flex: 1 1 100%;
        }
    }
</style>

<section class="page-intro">
    <span class="eyebrow">{{ $eyebrow }}</span>

    <h1>{{ $title }}</h1>

    <p>{{ $description }}</p>

    @if ($pageType === 'artworks')
        <form
            class="filter-bar"
            id="artworkFilters"
            action="{{ route('artworks.index') }}"
            method="GET"
        >
            {{-- Search --}}
            <label class="search-field">
                <span aria-hidden="true">⌕</span>
                <span class="visually-hidden">Search artworks</span>

                <input
                    type="search"
                    name="q"
                    value="{{ $query ?? '' }}"
                    placeholder="Search artworks or artists"
                >
            </label>

            {{-- Artwork format --}}
            <label>
                <span class="visually-hidden">Artwork format</span>

                <select name="artwork_type" aria-label="Artwork format">
                    <option value="">All Artworks</option>

                    <option
                        value="physical"
                        @selected(($artworkFormat ?? '') === 'physical')
                    >
                        Physical Artwork
                    </option>

                    <option
                        value="digital"
                        @selected(($artworkFormat ?? '') === 'digital')
                    >
                        Digital Artwork
                    </option>
                </select>
            </label>

            {{-- Artistic medium --}}
            <label>
                <span class="visually-hidden">Artistic medium</span>

                <select name="medium" aria-label="Artistic medium">
                    <option value="">All Mediums</option>

                    <option
                        value="oil painting"
                        @selected(($mediumFilter ?? '') === 'oil painting')
                    >
                        Oil Painting
                    </option>

                    <option
                        value="watercolor painting"
                        @selected(($mediumFilter ?? '') === 'watercolor painting')
                    >
                        Watercolor Painting
                    </option>

                    <option
                        value="pastel painting"
                        @selected(($mediumFilter ?? '') === 'pastel painting')
                    >
                        Pastel Painting
                    </option>

                    <option
                        value="acrylic painting"
                        @selected(($mediumFilter ?? '') === 'acrylic painting')
                    >
                        Acrylic Painting
                    </option>

                    <option
                        value="ink wash painting"
                        @selected(($mediumFilter ?? '') === 'ink wash painting')
                    >
                        Ink Wash / Literati Painting
                    </option>

                    <option
                        value="encaustic painting"
                        @selected(($mediumFilter ?? '') === 'encaustic painting')
                    >
                        Encaustic / Hot Wax Painting
                    </option>

                    <option
                        value="spray painting"
                        @selected(($mediumFilter ?? '') === 'spray painting')
                    >
                        Spray Painting
                    </option>

                    <option
                        value="sketching"
                        @selected(($mediumFilter ?? '') === 'sketching')
                    >
                        Sketching
                    </option>

                    <option
                        value="photography"
                        @selected(($mediumFilter ?? '') === 'photography')
                    >
                        Photography
                    </option>

                    <option
                        value="digital painting"
                        @selected(($mediumFilter ?? '') === 'digital painting')
                    >
                        Digital Painting
                    </option>
                </select>
            </label>

            {{-- Maximum budget --}}
            <label>
                <span class="visually-hidden">Maximum budget</span>

                <select name="max_price" aria-label="Maximum budget">
                    <option value="">Any Price</option>

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

            {{-- Sorting --}}
            <label>
                <span class="visually-hidden">Sort artworks</span>

                <select name="sort" aria-label="Sort artworks">
                    <option
                        value="latest"
                        @selected(($sort ?? 'latest') === 'latest')
                    >
                        Newest
                    </option>

                    <option
                        value="oldest"
                        @selected(($sort ?? '') === 'oldest')
                    >
                        Oldest
                    </option>

                    <option
                        value="price-low"
                        @selected(($sort ?? '') === 'price-low')
                    >
                        Price: low to high
                    </option>

                    <option
                        value="price-high"
                        @selected(($sort ?? '') === 'price-high')
                    >
                        Price: high to low
                    </option>

                    <option
                        value="popular"
                        @selected(($sort ?? '') === 'popular')
                    >
                        Most popular
                    </option>
                </select>
            </label>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const filters = document.getElementById('artworkFilters');

                if (!filters) {
                    return;
                }

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
            });
        </script>
    @endif
</section>

<section class="section listing-section">
    @if (count($items) === 0)
        <div class="empty-state">
            <span>✳</span>

            <h2>No matches just yet</h2>

            <p>Try changing the format, medium, or budget.</p>

            <a
                class="button button-dark"
                href="{{ route('artworks.index') }}"
            >
                See all artworks
            </a>
        </div>

    @elseif ($pageType === 'artworks')
        <div class="card-grid artwork-grid">
            @foreach ($items as $item)
                <a
                    class="art-card"
                    href="{{ route('artworks.show', $item['slug']) }}"
                >
                    <div
                        class="art-card-image artfy-card-visual {{ empty($item['image']) ? 'artwork-art ' . ($item['color'] ?? 'green') : '' }}"
                    >
                        @if (!empty($item['image']))
                            <img
                                class="artfy-card-photo"
                                src="{{ asset('storage/' . $item['image']) }}"
                                alt="{{ $item['title'] }}"
                                loading="lazy"
                            >
                        @endif

                        <span class="art-card-category">
                            {{ $item['category'] ?? 'Other' }}
                        </span>

                        <span class="like-count">
                            ♡ {{ $item['likes'] ?? 0 }}
                        </span>
                    </div>

                    <div class="art-card-info">
                        <div>
                            <h3>{{ $item['title'] }}</h3>

                            <p>
                                by
                                <span>
                                    {{ $item['artist'] ?? 'Artfy Artist' }}
                                </span>
                            </p>
                        </div>

                        <strong>
                            @if (isset($item['price']) && $item['price'] !== null)
                                ৳{{ number_format((float) $item['price'], 2) }}
                            @else
                                Price not listed
                            @endif
                        </strong>
                    </div>
                </a>
            @endforeach
        </div>

    @elseif ($pageType === 'artists')
        <div class="artist-grid artist-list">
            @foreach ($items as $item)
                <a
                    class="artist-card"
                    href="{{ route('artists.show', $item['slug']) }}"
                >
                    <span class="artist-avatar artwork-art {{ $item['color'] ?? 'green' }}">
                        @if (!empty($item['image']))
                            <img
                                class="artfy-artist-photo"
                                src="{{ asset('storage/' . $item['image']) }}"
                                alt="{{ $item['name'] }}"
                                loading="lazy"
                            >
                        @else
                            {{ strtoupper(substr($item['name'], 0, 1)) }}
                        @endif
                    </span>

                    <span class="artist-card-name">
                        {{ $item['name'] }}
                    </span>

                    <span class="artist-card-meta">
                        {{ $item['discipline'] ?? 'Independent artist' }}
                    </span>

                    <span class="artist-card-arrow">↗</span>
                </a>
            @endforeach
        </div>

    @else
        <div class="exhibition-grid">
            @foreach ($items as $item)
                <a
                    class="exhibition-card artwork-art {{ $item['color'] ?? 'green' }}"
                    href="{{ route('exhibitions.show', $item['slug']) }}"
                >
                    <span class="preview-tag">Online exhibition</span>

                    <strong>{{ $item['title'] }}</strong>

                    <small>{{ $item['subtitle'] }}</small>

                    <span class="preview-arrow">↗</span>
                </a>
            @endforeach
        </div>
    @endif
</section>

@endsection
