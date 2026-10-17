 @extends('artfy.layout')

@section('title', $title)

@section('content')

<style>
    /*
    |--------------------------------------------------------------------------
    | Uploaded artwork display
    |--------------------------------------------------------------------------
    | Apply the clean background only when the visual contains an uploaded
    | image. Keep the existing decorative styles for sample artwork pages.
    */

    .detail-art:has(.detail-uploaded-image) {
        background: #f8f6f2 !important;
        background-image: none !important;
        padding: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-sizing: border-box;
        isolation: isolate;
    }

    .detail-art:has(.detail-uploaded-image)::before,
    .detail-art:has(.detail-uploaded-image)::after {
        display: none !important;
        content: none !important;
        background: none !important;
    }

    .detail-uploaded-image {
        display: block;
        width: 100%;
        height: 100%;
        min-width: 0;
        min-height: 0;
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        object-position: center;
        background-color: #f8f6f2;
        position: relative;
        z-index: 2;
    }

    .detail-artist-name {
        color: inherit;
        text-decoration: none;
    }

    .detail-artist-name:hover {
        text-decoration: underline;
    }

    @media (max-width: 600px) {
        .detail-art:has(.detail-uploaded-image) {
            padding: 12px;
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
        <div class="detail-art artwork-art {{ $item['color'] ?? 'green' }}">

            @if ($pageType === 'artwork' && !empty($item['image']))

                {{-- Uploaded artwork: display the original image clearly --}}
                <img
                    class="detail-uploaded-image"
                    src="{{ asset('storage/' . $item['image']) }}"
                    alt="{{ $item['title'] ?? 'Uploaded artwork' }}"
                >

            @else

                {{-- Preserve the decorative design for sample pages --}}
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
                                {{ $item['artist'] }} ↗
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

                    <span class="detail-price">

                        @if (
                            isset($item['price']) &&
                            $item['price'] !== null &&
                            $item['price'] !== ''
                        )

                            ${{ number_format((float) $item['price'], 2) }}

                        @else

                            Price not listed

                        @endif

                    </span>

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