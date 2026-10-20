@extends('artfy.layout')

@section('title', $title)

@section('content')

<style>
    /* Artwork image container */
    .detail-art {
        position: relative;
        min-width: 0;
        min-height: 440px;
        overflow: hidden;
        box-sizing: border-box;
        border-radius: 5px;
    }

    /* Clean frame when an artist has uploaded a real image */
    .detail-art:has(.detail-uploaded-image) {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border: none;
        background: #f8f6f2 !important;
        background-image: none !important;
        isolation: isolate;
    }

    /* Remove decorative shapes from uploaded artwork only */
    .detail-art:has(.detail-uploaded-image)::before,
    .detail-art:has(.detail-uploaded-image)::after {
        content: none !important;
        display: none !important;
        background: none !important;
        background-image: none !important;
    }

    /* Display the artist's complete image */
    .detail-art .detail-uploaded-image {
        display: block;
        width: 100%;
        height: 100%;
        min-width: 0;
        min-height: 0;
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        object-position: center;
        border: none;
        border-radius: 0;
        box-shadow: none;
        background: transparent;
        position: relative;
        z-index: 1;
    }

    .detail-artist-name {
        color: inherit;
        text-decoration: none;
    }

    .detail-artist-name:hover {
        text-decoration: underline;
    }

    /* Sold artwork status */
    .detail-sold-label {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 24px;
        background: #e5e2d8;
        color: #414c2c;
        border: 1px solid #72795f;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: default;
    }

    @media (max-width: 600px) {
        .detail-art {
            min-height: 300px;
        }

        .detail-art:has(.detail-uploaded-image) {
            padding: 0;
        }
    }
</style>

<section class="detail-page">

    <a
        class="back-link"
        href="{{ match ($pageType) {
            'artwork' => route('artworks.index'),
            'artist' => route('artists.index'),
            default => route('exhibitions.index')
        } }}"
    >
        ← Back to {{ $pageType === 'artwork'
            ? 'artworks'
            : ($pageType === 'artist' ? 'artists' : 'exhibitions') }}
    </a>

    <div class="detail-layout">

        {{-- Artwork, artist, or exhibition visual --}}
        <div class="detail-art {{ $item['color'] ?? 'green' }}">

            @if ($pageType === 'artwork' && !empty($item['image']))

                <img
                    class="detail-uploaded-image"
                    src="{{ asset('storage/' . $item['image']) }}"
                    alt="{{ $item['title'] ?? 'Uploaded artwork' }}"
                >

            @else

                <span class="detail-stamp">{{ $eyebrow }}</span>

            @endif

        </div>

        {{-- Details and information --}}
        <div class="detail-copy">

            <span class="eyebrow">{{ $eyebrow }}</span>

            <h1>{{ $title }}</h1>

            <p class="detail-description">{{ $description }}</p>

            @if ($pageType === 'artwork')

                <div class="detail-facts">

                    <span>
                        Artist

                        @if (!empty($item['artist_slug']))

                            <a
                                class="detail-artist-name"
                                href="{{ route('artists.show', $item['artist_slug']) }}"
                            >
                                {{ $item['artist'] ?? 'Artfy Artist' }} ↗
                            </a>

                        @else

                            <strong>
                                {{ $item['artist'] ?? 'Artfy Artist' }}
                            </strong>

                        @endif
                    </span>

                    <span>
                        Category
                        <strong>
                            {{ $item['category'] ?? 'Uncategorized' }}
                        </strong>
                    </span>

                    <span>
                        Medium
                        <strong>
                            {{ $item['type'] ?? 'Not specified' }}
                        </strong>
                    </span>

                </div>

                <div class="detail-actions">

                    {{-- Artwork price in Bangladeshi Taka --}}
                    <span class="detail-price">

                        @if (
                            isset($item['price']) &&
                            $item['price'] !== null &&
                            $item['price'] !== ''
                        )

                            ৳{{ number_format((float) $item['price'], 2) }}

                        @else

                            Price not listed

                        @endif

                    </span>

                    {{-- Purchase status and Buy Now button --}}
                    @php
                        $isSold = !empty($item['is_sold'])
                            || ($item['status'] ?? '') === 'sold';
                    @endphp

                    @if ($isSold)

                        <span
                            class="detail-sold-label"
                            aria-label="This artwork has been sold"
                        >
                            Sold
                        </span>

                    @elseif (
                        !empty($item['is_uploaded']) &&
                        isset($item['price']) &&
                        is_numeric($item['price']) &&
                        (float) $item['price'] > 0
                    )

                        @auth

                            @if (auth()->user()->isArtLover())

                                <a
                                    class="button button-dark"
                                    href="{{ route('artworks.purchase.create', $item['slug']) }}"
                                >
                                    Buy Now <span>→</span>
                                </a>

                            @endif

                        @else

                            <a
                                class="button button-dark"
                                href="{{ route('login') }}"
                            >
                                Log in to buy <span>→</span>
                            </a>

                        @endauth

                    @endif

                    {{-- Favorites --}}
                    <a
                        class="button button-dark"
                        href="{{ route('favorites.index') }}"
                    >
                        Save to favorites <span>♡</span>
                    </a>

                </div>

            @elseif ($pageType === 'artist')

                <div class="detail-facts">

                    <span>
                        Based in
                        <strong>
                            {{ $item['discipline'] ?? 'Independent artist' }}
                        </strong>
                    </span>

                </div>

                <a
                    class="button button-dark"
                    href="{{ route('artworks.index', ['q' => $item['name']]) }}"
                >
                    Explore {{ $item['name'] }}’s work <span>→</span>
                </a>

            @else

                <a
                    class="button button-dark"
                    href="{{ route('artworks.index') }}"
                >
                    Explore artworks <span>→</span>
                </a>

            @endif

        </div>

    </div>

</section>

@endsection
