@extends('artfy.layout')

@section('title', ($item['title'] ?? 'Artwork') . ' | Artfy')

@section('content')
@php
    $image = $item['image'] ?? null;

    $imageUrl = $image
        ? (
            filter_var($image, FILTER_VALIDATE_URL)
                ? $image
                : asset('storage/' . ltrim($image, '/'))
        )
        : null;

    $slug = $item['slug'] ?? null;

    $isSold = !empty($item['is_sold'])
        || (($item['status'] ?? null) === 'sold');

    $canManage = !empty($item['can_manage'])
        && !empty($item['is_uploaded'])
        && !empty($item['id']);
@endphp

<section class="detail-page">

    {{-- Return to the logged-in user's Artist Profile --}}
    <a class="back-link" href="{{ route('my-profile') }}">
        ← Back to My Artworks
    </a>

    @if (session('success'))
        <div role="status" class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert" class="alert alert-error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="detail-layout">

        <div class="detail-image">
            @if ($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $item['title'] ?? 'Artwork' }}"
                    loading="eager"
                >
            @else
                <div class="detail-image-placeholder">
                    Artwork image unavailable
                </div>
            @endif
        </div>

        <div class="detail-copy">

            <p class="eyebrow">
                {{ $eyebrow ?? 'ARTWORK DETAILS' }}
            </p>

            <h1>{{ $item['title'] ?? 'Untitled artwork' }}</h1>

            @if (!empty($item['artist']))
                <p class="detail-artist">
                    By
                    @if (!empty($item['artist_slug']))
                        <a href="{{ route('artists.show', $item['artist_slug']) }}">
                            {{ $item['artist'] }}
                        </a>
                    @else
                        {{ $item['artist'] }}
                    @endif
                </p>
            @endif

            <div class="detail-description">{{ $item['description'] ?? $description ?? '' }}</div>

            <div class="detail-facts">

                @if (!empty($item['category']))
                    <div class="detail-fact">
                        <span>Category</span>
                        <strong>{{ $item['category'] }}</strong>
                    </div>
                @endif

                @if (!empty($item['type']))
                    <div class="detail-fact">
                        <span>Medium</span>
                        <strong>{{ $item['type'] }}</strong>
                    </div>
                @endif

                @if (!empty($item['status']))
                    <div class="detail-fact">
                        <span>Availability</span>
                        <strong>{{ $isSold ? 'Sold' : 'Available' }}</strong>
                    </div>
                @endif

            </div>

            <div class="detail-actions">

                @if (isset($item['price']) && $item['price'] !== null)
                    <p class="detail-price">
                        ৳{{ number_format((float) $item['price'], 2) }}
                    </p>
                @endif

                @if ($isSold)
                    <button class="button button-dark" type="button" disabled>
                        Artwork Sold
                    </button>
                @elseif (!empty($slug))
                    @auth
                        <a
                            class="button button-dark"
                            href="{{ route('artworks.purchase.create', $slug) }}"
                        >
                            Buy Artwork →
                        </a>
                    @else
                        <a
                            class="button button-dark"
                            href="{{ route('login', ['redirect' => url()->current()]) }}"
                        >
                            Log in to Buy →
                        </a>
                    @endauth
                @endif

                <a class="button button-light" href="{{ route('favorites.index') }}">
                    View Favorites
                </a>

            </div>

            @if ($canManage)
                <div class="artwork-management-actions">

                    <a
                        class="button button-dark"
                        href="{{ route('artist.artworks.edit', $item['id']) }}"
                    >
                        Edit Artwork
                    </a>

                    <form
                        action="{{ route('artist.artworks.destroy', $item['id']) }}"
                        method="POST"
                        onsubmit="return confirm('Delete this artwork permanently? This action cannot be undone.');"
                    >
                        @csrf
                        @method('DELETE')

                        <button class="button button-light" type="submit">
                            Delete Artwork
                        </button>
                    </form>

                </div>
            @endif

        </div>
    </div>
</section>

<style>
    .detail-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(280px, 0.9fr);
        gap: clamp(24px, 5vw, 64px);
        align-items: start;
        margin-top: 24px;
    }

    .detail-image {
        min-width: 0;
        overflow: hidden;
        border-radius: 12px;
        background: #f4f1eb;
    }

    .detail-image img {
        display: block;
        width: 100%;
        max-height: 760px;
        object-fit: contain;
    }

    .detail-image-placeholder {
        padding: 80px 20px;
        text-align: center;
    }

    .detail-copy h1 {
        margin: 10px 0 14px;
        overflow-wrap: anywhere;
    }

    .detail-artist {
        margin-bottom: 20px;
    }

    .detail-description {
        line-height: 1.8;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .detail-facts {
        display: grid;
        gap: 12px;
        margin: 24px 0;
    }

    .detail-fact {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e7e3dc;
    }

    .detail-fact span {
        opacity: 0.7;
    }

    .detail-price {
        width: 100%;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0 0 6px;
    }

    .detail-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        margin-top: 24px;
    }

    .artwork-management-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        width: 100%;
        margin-top: 16px;
    }

    .artwork-management-actions form {
        margin: 0;
    }

    .detail-actions button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .alert {
        padding: 12px 16px;
        margin: 16px 0;
        border-radius: 8px;
    }

    .alert-success {
        background: #edf7ee;
        color: #205c2d;
    }

    .alert-error {
        background: #fff0f0;
        color: #9b2020;
    }

    .alert ul {
        margin-bottom: 0;
        padding-left: 22px;
    }

    @media (max-width: 760px) {
        .detail-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .detail-image img {
            max-height: 520px;
        }
    }
</style>
@endsection
