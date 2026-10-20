@extends(($adminPreview ?? false) ? 'artfy.admin-artist-layout' : 'artfy.layout')

@section('title', ($profile?->artist_name ?: $user->name) . ' | Artfy')

@section('content')

@php
    $artistName = $profile?->artist_name ?: $user->name;
    $isAdminPreview = $adminPreview ?? false;

    $allArtworks = $artworks ?? collect();

    // Use the controller's collections when available.
    // Fallbacks also support the admin's artist-profile preview.
    $soldArtworks = $soldArtworks ?? $allArtworks->filter(function ($artwork) {
        return (bool) $artwork->is_sold
            || $artwork->status === 'sold';
    })->values();

    $availableArtworks = $availableArtworks ?? $allArtworks->filter(function ($artwork) {
        return !(bool) $artwork->is_sold
            && $artwork->status === 'available'
            && $artwork->moderation_status === 'approved';
    })->values();

    $otherArtworks = $otherArtworks ?? $allArtworks->filter(function ($artwork) {
        $isSold = (bool) $artwork->is_sold
            || $artwork->status === 'sold';

        $isAvailable = !(bool) $artwork->is_sold
            && $artwork->status === 'available'
            && $artwork->moderation_status === 'approved';

        return !$isSold && !$isAvailable;
    })->values();

    $totalSales = $totalSales ?? 0;

    $artworkCount = $allArtworks->count();
    $availableCount = $availableArtworks->count();
    $soldCount = $soldArtworks->count();
@endphp

<style>
    .artist-profile-page {
        max-width: 1240px;
        margin: 0 auto;
        padding: 30px 24px 80px;
        color: #292b23;
    }

    .artist-cover {
        position: relative;
        height: 300px;
        overflow: hidden;
        border-radius: 5px;
        background:
            linear-gradient(120deg, rgba(42, 49, 32, .86), rgba(86, 97, 58, .48)),
            #737b5c;
    }

    .artist-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .artist-cover-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,.8);
        font-family: Georgia, serif;
        font-size: clamp(30px, 5vw, 58px);
        font-style: italic;
        letter-spacing: 2px;
    }

    .artist-profile-intro {
        display: flex;
        align-items: flex-end;
        gap: 26px;
        padding: 0 30px;
        margin-top: -65px;
        position: relative;
        flex-wrap: wrap;
    }

    .artist-avatar {
        width: 140px;
        height: 140px;
        flex: 0 0 140px;
        border: 6px solid #f8f6f2;
        border-radius: 50%;
        overflow: hidden;
        background: #e6e3d7;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #55613a;
        font-family: Georgia, serif;
        font-size: 48px;
    }

    .artist-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .artist-heading {
        flex: 1;
        min-width: 220px;
        padding: 0 0 12px;
    }

    .artist-heading .artist-label {
        color: #72795f;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .artist-heading h1 {
        margin: 5px 0 8px;
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(30px, 4vw, 43px);
        font-weight: 500;
        line-height: 1.15;
    }

    .artist-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 18px;
        color: #727267;
        font-size: 13px;
    }

    .artist-profile-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding-bottom: 12px;
    }

    .artist-profile-actions .button {
        text-decoration: none;
        cursor: pointer;
    }

    .artist-profile-actions .button-outline {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 18px;
        border: 1px solid #c9c9b9;
        border-radius: 3px;
        color: #424a32;
        font-size: 13px;
        font-weight: 600;
    }

    .artist-profile-actions .button-outline:hover {
        background: #eeede5;
    }

    .artist-bio {
        max-width: 780px;
        margin: 25px 30px 0;
        color: #68695f;
        font-size: 14px;
        line-height: 1.9;
        white-space: pre-line;
    }

    .artist-profile-link {
        display: inline-block;
        margin: 12px 30px 0;
        color: #55613a;
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .artist-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 36px;
        margin: 30px 30px 0;
        padding: 22px 0;
        border-top: 1px solid #e5e2d8;
        border-bottom: 1px solid #e5e2d8;
    }

    .artist-stat strong {
        display: block;
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 26px;
        font-weight: 500;
    }

    .artist-stat span {
        color: #77776d;
        font-size: 11px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .artist-portfolio {
        margin-top: 48px;
        scroll-margin-top: 30px;
    }

    .artist-portfolio-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 24px;
    }

    .artist-portfolio-header .eyebrow {
        color: #72795f;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .artist-portfolio-header h2 {
        margin: 7px 0 0;
        font-family: 'Playfair Display', Georgia, serif;
        font-size: clamp(26px, 3vw, 36px);
        font-weight: 500;
    }

    .artist-portfolio-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 24px;
    }

    .artist-portfolio-tabs a {
        display: inline-block;
        padding: 10px 15px;
        border: 1px solid #d5d2c6;
        color: #414c2c;
        font-size: 12px;
        text-decoration: none;
        transition: background .2s ease;
    }

    .artist-portfolio-tabs a:hover {
        background: #eeede5;
    }

    .artist-artwork-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 28px 22px;
    }

    .artist-artwork-card {
        display: block;
        min-width: 0;
        color: inherit;
        text-decoration: none;
    }

    .artist-artwork-image {
        position: relative;
        aspect-ratio: 4 / 4.5;
        overflow: hidden;
        background: #e9e6dc;
    }

    .artist-artwork-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .35s ease;
    }

    .artist-artwork-card:hover .artist-artwork-image img {
        transform: scale(1.04);
    }

    .artist-artwork-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        color: #737b5c;
        font-family: Georgia, serif;
        font-size: 24px;
        font-style: italic;
        text-align: center;
    }

    .artist-sold-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 2;
        padding: 8px 12px;
        background: #414c2c;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .artist-pending-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 2;
        padding: 8px 12px;
        background: #f8f6f2;
        color: #414c2c;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .5px;
    }

    .artist-artwork-info {
        padding-top: 13px;
    }

    .artist-artwork-info h3 {
        margin: 0 0 6px;
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 19px;
        font-weight: 500;
    }

    .artist-artwork-info p {
        margin: 0;
        color: #77776d;
        font-size: 12px;
        line-height: 1.6;
    }

    .artist-artwork-price {
        display: block;
        margin-top: 9px;
        font-size: 14px;
        font-weight: 600;
    }

    .artist-other-status {
        display: block;
        margin-top: 8px;
        color: #72795f;
        font-size: 11px;
    }

    .artist-empty-portfolio {
        padding: 48px 20px;
        border: 1px dashed #d5d2c6;
        text-align: center;
        background: #fbfaf7;
    }

    .artist-empty-portfolio h3 {
        margin: 0 0 10px;
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 25px;
        font-weight: 500;
    }

    .artist-empty-portfolio p {
        margin: 0 0 20px;
        color: #77776d;
        font-size: 13px;
        line-height: 1.7;
    }

    @media (max-width: 760px) {
        .artist-profile-page {
            padding: 20px 16px 55px;
        }

        .artist-cover {
            height: 220px;
        }

        .artist-profile-intro {
            padding: 0 10px;
            gap: 15px;
            margin-top: -48px;
        }

        .artist-avatar {
            width: 105px;
            height: 105px;
            flex-basis: 105px;
            font-size: 36px;
        }

        .artist-heading {
            min-width: 150px;
        }

        .artist-profile-actions {
            width: 100%;
            padding: 0;
        }

        .artist-bio,
        .artist-profile-link,
        .artist-stats {
            margin-left: 10px;
            margin-right: 10px;
        }

        .artist-stats {
            gap: 25px;
        }

        .artist-artwork-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px 14px;
        }

        .artist-portfolio-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 420px) {
        .artist-artwork-grid {
            grid-template-columns: 1fr;
        }

        .artist-artwork-image {
            aspect-ratio: 4 / 3.5;
        }
    }
</style>

<div class="artist-profile-page">

    {{-- Cover banner --}}
    <section class="artist-cover">
        @if ($profile?->cover_photo)
            <img
                src="{{ asset('storage/' . $profile->cover_photo) }}"
                alt="{{ $artistName }} cover photo"
            >
        @else
            <div class="artist-cover-placeholder">
                A space for creativity
            </div>
        @endif
    </section>

    {{-- Artist identity --}}
    <section class="artist-profile-intro">
        <div class="artist-avatar">
            @if ($profile?->profile_photo)
                <img
                    src="{{ asset('storage/' . $profile->profile_photo) }}"
                    alt="{{ $artistName }} profile photo"
                >
            @else
                {{ mb_strtoupper(mb_substr($artistName, 0, 1)) }}
            @endif
        </div>

        <div class="artist-heading">
            <div class="artist-label">ARTFY ARTIST</div>
            <h1>{{ $artistName }}</h1>

            <div class="artist-meta">
                @if ($profile?->art_category)
                    <span>{{ $profile->art_category }}</span>
                @endif

                @if ($profile?->location)
                    <span>⌖ {{ $profile->location }}</span>
                @endif
            </div>
        </div>

        <div class="artist-profile-actions">
            @if ($isAdminPreview)
                <a
                    class="button button-outline"
                    href="{{ route('admin.artists.index') }}"
                >
                    ← Back to Artists
                </a>
            @else
                <a
                    class="button button-outline"
                    href="{{ route('artist-profile.edit') }}"
                >
                    Edit Profile
                </a>

                <a
                    class="button button-dark"
                    href="{{ route('artist.artworks.create') }}"
                >
                    + Add Your Work
                </a>
            @endif
        </div>
    </section>

    {{-- Artist bio --}}
    @if ($profile?->bio)
        <p class="artist-bio">{{ $profile->bio }}</p>
    @else
        <p class="artist-bio">
            Welcome to my Artfy profile. This is where I share my artistic
            journey and original work. You can edit your profile to tell
            visitors more about yourself.
        </p>
    @endif

    @if ($profile?->portfolio_url)
        <a
            class="artist-profile-link"
            href="{{ $profile->portfolio_url }}"
            target="_blank"
            rel="noopener noreferrer"
        >
            Visit external portfolio ↗
        </a>
    @endif

    {{-- Portfolio statistics --}}
    <div class="artist-stats">
        <div class="artist-stat">
            <strong>{{ $artworkCount }}</strong>
            <span>Total Artworks</span>
        </div>

        <div class="artist-stat">
            <strong>{{ $availableCount }}</strong>
            <span>Available</span>
        </div>

        <div class="artist-stat">
            <strong>{{ $soldCount }}</strong>
            <span>Sold Out</span>
        </div>

        @unless ($isAdminPreview)
            <div class="artist-stat">
                <strong>{{ $totalSales }}</strong>
                <span>Confirmed Sales</span>
            </div>
        @endunless
    </div>

    {{-- Artwork portfolio --}}
    <section class="artist-portfolio" id="my-artworks">
        <div class="artist-portfolio-header">
            <div>
                <div class="eyebrow">THE COLLECTION</div>
                <h2>My Artworks</h2>
            </div>

            @unless ($isAdminPreview)
                <a
                    class="button button-dark"
                    href="{{ route('artist.artworks.create') }}"
                >
                    Add Your Work →
                </a>
            @endunless
        </div>

        {{-- Quick links to the artwork sections --}}
        <nav class="artist-portfolio-tabs" aria-label="Artwork sections">
            <a href="#available-artworks">
                Available ({{ $availableCount }})
            </a>

            <a href="#sold-artworks">
                Sold Out ({{ $soldCount }})
            </a>

            @if ($otherArtworks->isNotEmpty())
                <a href="#other-artworks">
                    Pending / Unavailable ({{ $otherArtworks->count() }})
                </a>
            @endif
        </nav>

        {{-- AVAILABLE ARTWORKS --}}
        <section class="artist-portfolio" id="available-artworks">
            <div class="artist-portfolio-header">
                <div>
                    <div class="eyebrow">READY FOR COLLECTORS</div>
                    <h2>Available Artworks</h2>
                </div>
            </div>

            @if ($availableArtworks->isNotEmpty())
                <div class="artist-artwork-grid">
                    @foreach ($availableArtworks as $artwork)
                        <a
                            class="artist-artwork-card"
                            href="{{ route('artworks.show', ['slug' => 'uploaded-' . $artwork->id]) }}"
                        >
                            <div class="artist-artwork-image">
                                @if ($artwork->image)
                                    <img
                                        src="{{ asset('storage/' . $artwork->image) }}"
                                        alt="{{ $artwork->title }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="artist-artwork-image-placeholder">
                                        {{ $artwork->title }}
                                    </div>
                                @endif
                            </div>

                            <div class="artist-artwork-info">
                                <h3>{{ $artwork->title }}</h3>

                                <p>
                                    {{ $artwork->category ?: 'Original artwork' }}
                                    @if ($artwork->medium)
                                        · {{ $artwork->medium }}
                                    @endif
                                </p>

                                @if (!is_null($artwork->price))
                                    <span class="artist-artwork-price">
                                        ৳{{ number_format((float) $artwork->price, 2) }}
                                    </span>
                                @else
                                    <span class="artist-other-status">
                                        Price not listed
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="artist-empty-portfolio">
                    <h3>No available artworks right now</h3>
                    <p>
                        When your approved artworks are available for purchase,
                        they will appear in this section.
                    </p>

                    @unless ($isAdminPreview)
                        <a
                            class="button button-dark"
                            href="{{ route('artist.artworks.create') }}"
                        >
                            Add Your Work →
                        </a>
                    @endunless
                </div>
            @endif
        </section>

        {{-- SOLD OUT ARTWORKS --}}
        <section class="artist-portfolio" id="sold-artworks">
            <div class="artist-portfolio-header">
                <div>
                    <div class="eyebrow">COLLECTED AND SOLD</div>
                    <h2>Sold Out</h2>
                </div>
            </div>

            @if ($soldArtworks->isNotEmpty())
                <div class="artist-artwork-grid">
                    @foreach ($soldArtworks as $artwork)
                        <a
                            class="artist-artwork-card"
                            href="{{ route('artworks.show', ['slug' => 'uploaded-' . $artwork->id]) }}"
                        >
                            <div class="artist-artwork-image">
                                @if ($artwork->image)
                                    <img
                                        src="{{ asset('storage/' . $artwork->image) }}"
                                        alt="{{ $artwork->title }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="artist-artwork-image-placeholder">
                                        {{ $artwork->title }}
                                    </div>
                                @endif

                                <span class="artist-sold-badge">
                                    Sold Out
                                </span>
                            </div>

                            <div class="artist-artwork-info">
                                <h3>{{ $artwork->title }}</h3>

                                <p>
                                    {{ $artwork->category ?: 'Original artwork' }}
                                    @if ($artwork->medium)
                                        · {{ $artwork->medium }}
                                    @endif
                                </p>

                                @if (!is_null($artwork->price))
                                    <span class="artist-artwork-price">
                                        ৳{{ number_format((float) $artwork->price, 2) }}
                                    </span>
                                @endif

                                <span class="artist-other-status">
                                    This artwork has been sold and is no longer available.
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="artist-empty-portfolio">
                    <h3>No artworks sold yet</h3>
                    <p>
                        When a buyer completes a purchase and the payment is
                        verified, that artwork will appear here automatically.
                    </p>
                </div>
            @endif
        </section>

        {{-- PENDING OR OTHERWISE UNAVAILABLE ARTWORKS --}}
        @if ($otherArtworks->isNotEmpty())
            <section class="artist-portfolio" id="other-artworks">
                <div class="artist-portfolio-header">
                    <div>
                        <div class="eyebrow">UNDER REVIEW OR UNAVAILABLE</div>
                        <h2>Pending / Unavailable</h2>
                    </div>
                </div>

                <div class="artist-artwork-grid">
                    @foreach ($otherArtworks as $artwork)
                        <a
                            class="artist-artwork-card"
                            href="{{ route('artworks.show', ['slug' => 'uploaded-' . $artwork->id]) }}"
                        >
                            <div class="artist-artwork-image">
                                @if ($artwork->image)
                                    <img
                                        src="{{ asset('storage/' . $artwork->image) }}"
                                        alt="{{ $artwork->title }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="artist-artwork-image-placeholder">
                                        {{ $artwork->title }}
                                    </div>
                                @endif

                                <span class="artist-pending-badge">
                                    {{ ucfirst(str_replace('_', ' ', $artwork->moderation_status ?: $artwork->status ?: 'Unavailable')) }}
                                </span>
                            </div>

                            <div class="artist-artwork-info">
                                <h3>{{ $artwork->title }}</h3>

                                <p>
                                    {{ $artwork->category ?: 'Original artwork' }}
                                    @if ($artwork->medium)
                                        · {{ $artwork->medium }}
                                    @endif
                                </p>

                                @if (!is_null($artwork->price))
                                    <span class="artist-artwork-price">
                                        ৳{{ number_format((float) $artwork->price, 2) }}
                                    </span>
                                @endif

                                <span class="artist-other-status">
                                    This artwork is not currently available for purchase.
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </section>

</div>
@endsection
