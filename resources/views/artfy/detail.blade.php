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

    $title = $item['title'] ?? 'Untitled artwork';
    $artist = $item['artist'] ?? null;
    $description = $item['description'] ?? $description ?? '';
    $price = $item['price'] ?? null;
@endphp

<section class="artwork-detail-page">

    <div class="artwork-detail-topbar">
        <a class="artwork-back-link" href="{{ url()->previous() }}">
            <span aria-hidden="true">←</span>
            <span>Go back</span>
        </a>

        <span class="gallery-label">ARTFY / ARTWORK</span>
    </div>

    @if (session('success'))
        <div role="status" class="detail-alert detail-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="detail-alert detail-alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert" class="detail-alert detail-alert-error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="artwork-detail-layout">

        {{-- Artwork preview --}}
        <div class="artwork-visual-column">
            <div class="artwork-visual-frame">

                <div class="artwork-frame-label">
                    <span>ARTWORK NO. {{ $item['id'] ?? '—' }}</span>
                    <span class="artwork-status {{ $isSold ? 'is-sold' : 'is-available' }}">
                        <span class="status-dot"></span>
                        {{ $isSold ? 'Sold' : 'Available' }}
                    </span>
                </div>

                <div class="artwork-image-stage">
                    @if ($imageUrl)
                        <img
                            class="artwork-main-image"
                            src="{{ $imageUrl }}"
                            alt="{{ $title }}"
                            fetchpriority="high"
                        >
                    @else
                        <div class="artwork-image-empty">
                            <span class="empty-art-icon" aria-hidden="true">◇</span>
                            <p>Artwork image unavailable</p>
                        </div>
                    @endif
                </div>

                <div class="artwork-frame-footer">
                    <span>AN ORIGINAL CREATION</span>
                    <span>ARTFY GALLERY</span>
                </div>

            </div>

            <p class="artwork-image-caption">
                Every artwork tells a story. Discover the details behind this piece.
            </p>
        </div>

        {{-- Artwork information --}}
        <div class="artwork-information-column">

            <p class="artwork-eyebrow">
                {{ $eyebrow ?? 'THE ART COLLECTION' }}
            </p>

            <h1 class="artwork-detail-title">{{ $title }}</h1>

            @if ($artist)
                <div class="artwork-artist-line">
                    <span class="artist-line-decoration" aria-hidden="true"></span>
                    <span>Created by</span>

                    @if (!empty($item['artist_slug']))
                        <a href="{{ route('artists.show', $item['artist_slug']) }}">
                            {{ $artist }}
                        </a>
                    @else
                        <strong>{{ $artist }}</strong>
                    @endif
                </div>
            @endif

            <div class="artwork-title-divider"></div>

            <div class="artwork-description-block">
                <h2>About this artwork</h2>

                @if (filled($description))
                    <p class="artwork-detail-description">{{ $description }}</p>
                @else
                    <p class="artwork-detail-description artwork-no-description">
                        No description has been added for this artwork yet.
                    </p>
                @endif
            </div>

            <div class="artwork-specifications">
                @if (!empty($item['category']))
                    <div class="artwork-specification">
                        <span>Category</span>
                        <strong>{{ $item['category'] }}</strong>
                    </div>
                @endif

                @if (!empty($item['type']))
                    <div class="artwork-specification">
                        <span>Medium</span>
                        <strong>{{ $item['type'] }}</strong>
                    </div>
                @endif

                <div class="artwork-specification">
                    <span>Availability</span>
                    <strong>{{ $isSold ? 'Sold' : 'Available' }}</strong>
                </div>
            </div>

            <div class="artwork-purchase-panel">

                @if ($price !== null && $price !== '')
                    <div class="artwork-price-area">
                        <span class="artwork-price-label">
                            {{ $isSold ? 'Sale price' : 'Price' }}
                        </span>

                        <p class="artwork-detail-price">
                            <span class="currency-symbol">৳</span>{{ number_format((float) $price, 2) }}
                        </p>
                    </div>
                @endif

                <div class="artwork-primary-actions">
                    @if ($isSold)
                        <button class="artwork-primary-button" type="button" disabled>
                            Artwork Sold
                        </button>
                    @elseif (!empty($slug))
                        @auth
                            <a
                                class="artwork-primary-button"
                                href="{{ route('artworks.purchase.create', $slug) }}"
                            >
                                Buy this artwork
                                <span aria-hidden="true">↗</span>
                            </a>
                        @else
                            <a
                                class="artwork-primary-button"
                                href="{{ route('login', ['redirect' => url()->current()]) }}"
                            >
                                Log in to buy
                                <span aria-hidden="true">↗</span>
                            </a>
                        @endauth
                    @endif

                    <a class="artwork-secondary-button" href="{{ route('favorites.index') }}">
                        View favorites
                        <span aria-hidden="true">♡</span>
                    </a>
                </div>

                <p class="artwork-purchase-note">
                    <span aria-hidden="true">✳</span>
                    Discover art that speaks to you.
                </p>
            </div>

            @if ($canManage)
                <div class="artwork-owner-panel">
                    <div>
                        <h2>Manage your artwork</h2>
                        <p>Keep your artwork details up to date.</p>
                    </div>

                    <div class="artwork-owner-actions">
                        <a
                            class="artwork-owner-edit"
                            href="{{ route('artist.artworks.edit', $item['id']) }}"
                        >
                            Edit artwork
                        </a>

                        <form
                            action="{{ route('artist.artworks.destroy', $item['id']) }}"
                            method="POST"
                            onsubmit="return confirm('Delete this artwork permanently? This action cannot be undone.');"
                        >
                            @csrf
                            @method('DELETE')

                            <button class="artwork-owner-delete" type="submit">
                                Delete artwork
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <div class="artwork-detail-bottom">
        <span>ARTFY</span>
        <p>Celebrating creativity, one artwork at a time.</p>
    </div>

</section>

<style>
    .artwork-detail-page {
        --detail-cream: #f5f3e8;
        --detail-olive: #55613a;
        --detail-dark-olive: #414c2c;
        --detail-muted-olive: #72795f;
        --detail-border: #e5e2d6;
        --detail-ink: #292d22;

        width: min(1440px, 100%);
        margin: 0 auto;
        padding: clamp(22px, 4vw, 54px) clamp(16px, 4vw, 56px) 36px;
        color: var(--detail-ink);
        box-sizing: border-box;
    }

    .artwork-detail-page *,
    .artwork-detail-page *::before,
    .artwork-detail-page *::after {
        box-sizing: border-box;
    }

    .artwork-detail-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 22px;
        border-bottom: 1px solid var(--detail-border);
    }

    .artwork-back-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: var(--detail-olive);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 650;
        transition: color 0.2s ease;
    }

    .artwork-back-link:hover {
        color: var(--detail-dark-olive);
    }

    .artwork-back-link span:first-child {
        font-size: 1.3rem;
        transition: transform 0.2s ease;
    }

    .artwork-back-link:hover span:first-child {
        transform: translateX(-4px);
    }

    .gallery-label {
        color: var(--detail-muted-olive);
        font-size: 0.69rem;
        font-weight: 750;
        letter-spacing: 0.18em;
        text-align: right;
    }

    .artwork-detail-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.12fr) minmax(0, 0.88fr);
        gap: clamp(32px, 5vw, 76px);
        align-items: start;
        padding-top: clamp(30px, 4vw, 52px);
    }

    .artwork-visual-column,
    .artwork-information-column {
        min-width: 0;
    }

    .artwork-visual-frame {
        overflow: hidden;
        border: 1px solid #e7e4d9;
        border-radius: 5px;
        background: #eeece2;
        box-shadow: 0 18px 50px rgba(52, 59, 37, 0.07);
    }

    .artwork-frame-label,
    .artwork-frame-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 17px 20px;
        color: #747967;
        font-size: 0.64rem;
        font-weight: 750;
        letter-spacing: 0.12em;
    }

    .artwork-frame-label {
        border-bottom: 1px solid rgba(85, 97, 58, 0.12);
    }

    .artwork-frame-footer {
        border-top: 1px solid rgba(85, 97, 58, 0.12);
    }

    .artwork-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        letter-spacing: 0.04em;
        font-size: 0.72rem;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        display: inline-block;
        border-radius: 50%;
        background: #63834c;
    }

    .artwork-status.is-sold .status-dot {
        background: #a16b54;
    }

    .artwork-image-stage {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 340px;
        padding: clamp(16px, 3vw, 38px);
        background:
            radial-gradient(ellipse at center, rgba(255,255,255,0.72), rgba(245,243,232,0.22) 74%),
            #eeece2;
    }

    .artwork-main-image {
        display: block;
        width: 100%;
        height: auto;
        max-height: min(72vh, 760px);
        object-fit: contain;
        object-position: center;
        border-radius: 2px;
    }

    .artwork-image-empty {
        display: flex;
        min-height: 300px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        color: var(--detail-muted-olive);
        text-align: center;
    }

    .empty-art-icon {
        font-size: 3.5rem;
        line-height: 1;
    }

    .artwork-image-empty p {
        margin: 0;
    }

    .artwork-image-caption {
        margin: 15px 2px 0;
        color: #7b7d70;
        font-size: 0.8rem;
        line-height: 1.7;
    }

    .artwork-eyebrow {
        margin: 3px 0 16px;
        color: var(--detail-olive);
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.2em;
    }

    .artwork-detail-title {
        margin: 0;
        color: var(--detail-dark-olive);
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(2.2rem, 4.2vw, 4.1rem);
        font-weight: 500;
        line-height: 1.08;
        letter-spacing: -0.045em;
        overflow-wrap: anywhere;
    }

    .artwork-artist-line {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 22px;
        color: #76796b;
        font-size: 0.92rem;
    }

    .artwork-artist-line a,
    .artwork-artist-line strong {
        color: var(--detail-olive);
        font-weight: 750;
        text-decoration: none;
    }

    .artwork-artist-line a:hover {
        text-decoration: underline;
        text-underline-offset: 4px;
    }

    .artist-line-decoration {
        width: 24px;
        height: 1px;
        margin-right: 3px;
        background: var(--detail-muted-olive);
    }

    .artwork-title-divider {
        height: 1px;
        margin: 29px 0;
        background: var(--detail-border);
    }

    .artwork-description-block h2,
    .artwork-owner-panel h2 {
        margin: 0 0 12px;
        color: var(--detail-dark-olive);
        font-family: Georgia, "Times New Roman", serif;
        font-size: 1.28rem;
        font-weight: 500;
    }

    .artwork-detail-description {
        margin: 0;
        color: #686c5d;
        font-size: 0.96rem;
        line-height: 1.95;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .artwork-no-description {
        color: #898b80;
        font-style: italic;
    }

    .artwork-specifications {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 24px;
        margin-top: 29px;
        border-top: 1px solid var(--detail-border);
    }

    .artwork-specification {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 7px;
        padding: 16px 0;
        border-bottom: 1px solid var(--detail-border);
        min-width: 0;
    }

    .artwork-specification span {
        color: #818477;
        font-size: 0.76rem;
    }

    .artwork-specification strong {
        color: var(--detail-dark-olive);
        font-size: 0.9rem;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .artwork-purchase-panel {
        margin-top: 28px;
        padding: clamp(20px, 3vw, 28px);
        border: 1px solid #e2e0d3;
        border-radius: 5px;
        background: var(--detail-cream);
    }

    .artwork-price-label {
        display: block;
        margin-bottom: 8px;
        color: #777b6a;
        font-size: 0.77rem;
        font-weight: 650;
    }

    .artwork-detail-price {
        margin: 0;
        color: var(--detail-dark-olive);
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(1.8rem, 3vw, 2.45rem);
        font-weight: 600;
        letter-spacing: -0.03em;
        overflow-wrap: anywhere;
    }

    .currency-symbol {
        margin-right: 3px;
    }

    .artwork-primary-actions {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 10px;
        margin-top: 22px;
    }

    .artwork-primary-button,
    .artwork-secondary-button {
        display: inline-flex;
        min-height: 51px;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 14px 18px;
        border: 1px solid var(--detail-dark-olive);
        border-radius: 3px;
        font: inherit;
        font-size: 0.88rem;
        font-weight: 750;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition:
            background 0.2s ease,
            color 0.2s ease,
            border-color 0.2s ease,
            transform 0.2s ease;
    }

    .artwork-primary-button {
        color: #fff;
        background: var(--detail-dark-olive);
    }

    .artwork-primary-button:hover:not(:disabled) {
        color: #fff;
        background: var(--detail-olive);
        border-color: var(--detail-olive);
        transform: translateY(-1px);
    }

    .artwork-primary-button:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    .artwork-secondary-button {
        color: var(--detail-dark-olive);
        background: transparent;
    }

    .artwork-secondary-button:hover {
        color: var(--detail-dark-olive);
        background: #eae9dc;
    }

    .artwork-purchase-note {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin: 17px 0 0;
        color: #777b6a;
        font-size: 0.75rem;
        text-align: center;
        line-height: 1.6;
    }

    .artwork-purchase-note span {
        color: var(--detail-olive);
    }

    .artwork-owner-panel {
        margin-top: 22px;
        padding: 22px;
        border: 1px dashed #c9cbb9;
        border-radius: 5px;
    }

    .artwork-owner-panel h2 {
        margin-bottom: 6px;
        font-size: 1.15rem;
    }

    .artwork-owner-panel p {
        margin: 0;
        color: #777b6a;
        font-size: 0.83rem;
        line-height: 1.6;
    }

    .artwork-owner-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 17px;
    }

    .artwork-owner-actions form {
        margin: 0;
    }

    .artwork-owner-edit,
    .artwork-owner-delete {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        padding: 10px 14px;
        border-radius: 3px;
        font: inherit;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .artwork-owner-edit {
        border: 1px solid var(--detail-dark-olive);
        color: #fff;
        background: var(--detail-dark-olive);
    }

    .artwork-owner-delete {
        border: 1px solid #b87a6a;
        color: #934a3b;
        background: transparent;
    }

    .artwork-owner-edit:hover {
        color: #fff;
        background: var(--detail-olive);
    }

    .artwork-owner-delete:hover {
        background: #fff0ec;
    }

    .detail-alert {
        margin-top: 18px;
        padding: 14px 17px;
        border-radius: 4px;
        font-size: 0.9rem;
        line-height: 1.65;
    }

    .detail-alert-success {
        border: 1px solid #d1e5d1;
        color: #285c32;
        background: #edf7ee;
    }

    .detail-alert-error {
        border: 1px solid #efd3cf;
        color: #8e3029;
        background: #fff1ef;
    }

    .detail-alert ul {
        margin: 8px 0 0;
        padding-left: 22px;
    }

    .artwork-detail-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-top: 65px;
        padding-top: 22px;
        border-top: 1px solid var(--detail-border);
    }

    .artwork-detail-bottom span {
        color: var(--detail-olive);
        font-size: 0.72rem;
        font-weight: 850;
        letter-spacing: 0.2em;
    }

    .artwork-detail-bottom p {
        margin: 0;
        color: #818477;
        font-size: 0.8rem;
        text-align: right;
    }

    @media (min-width: 1100px) {
        .artwork-primary-actions {
            grid-template-columns: minmax(0, 1fr) minmax(0, 0.85fr);
        }
    }

    @media (max-width: 850px) {
        .artwork-detail-layout {
            grid-template-columns: minmax(0, 1fr);
            gap: 35px;
        }

        .artwork-visual-column {
            width: 100%;
        }

        .artwork-image-stage {
            min-height: 280px;
        }

        .artwork-main-image {
            max-height: 650px;
        }

        .artwork-detail-title {
            font-size: clamp(2.3rem, 7vw, 3.6rem);
        }

        .artwork-detail-bottom {
            margin-top: 42px;
        }
    }

    @media (max-width: 480px) {
        .artwork-detail-page {
            padding: 20px 15px 28px;
        }

        .gallery-label {
            font-size: 0.57rem;
            letter-spacing: 0.1em;
        }

        .artwork-frame-label,
        .artwork-frame-footer {
            padding: 13px;
            font-size: 0.57rem;
        }

        .artwork-image-stage {
            min-height: 230px;
            padding: 12px;
        }

        .artwork-detail-title {
            font-size: 2.35rem;
        }

        .artwork-specifications {
            gap: 0 15px;
        }

        .artwork-purchase-panel {
            padding: 18px;
        }

        .artwork-owner-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .artwork-owner-actions form,
        .artwork-owner-edit,
        .artwork-owner-delete {
            width: 100%;
        }

        .artwork-detail-bottom {
            align-items: flex-start;
            flex-direction: column;
        }

        .artwork-detail-bottom p {
            text-align: left;
        }
    }
</style>
@endsection
