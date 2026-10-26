@php
    $isAdminPreview = $adminPreview ?? false;

    /*
    |--------------------------------------------------------------------------
    | Artist profile data
    |--------------------------------------------------------------------------
    */
    $profileRecord = $profile ?? null;
    $userRecord = $user ?? null;

    $artistName = $profileRecord?->artist_name
        ?: $userRecord?->name
        ?: 'Artist';

    $profilePhoto = $profileRecord?->profile_photo;
    $coverPhoto = $profileRecord?->cover_photo;

    $discipline = $profileRecord?->art_category ?: 'Visual Artist';
    $location = $profileRecord?->location;
    $bio = $profileRecord?->bio ?? '';
    $portfolioUrl = $profileRecord?->portfolio_url;

    /*
    |--------------------------------------------------------------------------
    | Artwork collections
    |--------------------------------------------------------------------------
    */
    $allArtworks = collect($artworks ?? []);

    /*
     * Hide only Velvet Afterglow (artwork ID 7).
     * Do not delete the artwork or its order/payment records.
     */
    $allArtworks = $allArtworks
        ->reject(function ($artwork) {
            return (int) ($artwork->id ?? 0) === 7
                && ($artwork->title ?? '') === 'Velvet Afterglow';
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Available artworks
    |--------------------------------------------------------------------------
    */
    $availableArtworks = $allArtworks->filter(function ($artwork) {
        $status = strtolower((string) ($artwork->status ?? ''));

        $isSold = (bool) ($artwork->is_sold ?? false)
            || $status === 'sold';

        $isApproved = strtolower(
            (string) ($artwork->moderation_status ?? 'approved')
        ) === 'approved';

        return !$isSold
            && $status === 'available'
            && $isApproved;
    })->values();

    /*
    |--------------------------------------------------------------------------
    | Sold artworks
    |--------------------------------------------------------------------------
    */
    $soldArtworks = $allArtworks->filter(function ($artwork) {
        return (bool) ($artwork->is_sold ?? false)
            || strtolower((string) ($artwork->status ?? '')) === 'sold';
    })->values();

    /*
    |--------------------------------------------------------------------------
    | Other artworks
    |--------------------------------------------------------------------------
    */
    $otherArtworks = $allArtworks->reject(function ($artwork) use (
        $availableArtworks,
        $soldArtworks
    ) {
        return $availableArtworks->contains('id', $artwork->id)
            || $soldArtworks->contains('id', $artwork->id);
    })->values();

    /*
    |--------------------------------------------------------------------------
    | Image URL helper
    |--------------------------------------------------------------------------
    */
    $artworkImageUrl = function ($path) {
        if (blank($path)) {
            return null;
        }

        $path = trim((string) $path);

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $scheme = strtolower(
                (string) parse_url($path, PHP_URL_SCHEME)
            );

            return in_array($scheme, ['http', 'https'], true)
                ? $path
                : null;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return asset('storage/' . $path);
    };

    /*
    |--------------------------------------------------------------------------
    | Safe external portfolio link
    |--------------------------------------------------------------------------
    */
    $safePortfolioUrl = null;

    if (filled($portfolioUrl)) {
        $scheme = strtolower(
            (string) parse_url($portfolioUrl, PHP_URL_SCHEME)
        );

        if (in_array($scheme, ['http', 'https'], true)) {
            $safePortfolioUrl = $portfolioUrl;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */
    $firstCharacter = function_exists('mb_substr')
        ? mb_substr($artistName, 0, 1)
        : substr($artistName, 0, 1);

    $initial = function_exists('mb_strtoupper')
        ? mb_strtoupper($firstCharacter)
        : strtoupper($firstCharacter);

    $totalArtworks = $allArtworks->count();
    $totalAvailable = $availableArtworks->count();
    $totalSold = $soldArtworks->count();
@endphp

{{-- Same navigation, footer, and layout --}}
@extends('artfy.layout')

@section('title', $artistName . ' | Artfy')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/artist-public-profile.css') }}?v=20261024-3"
    >
@endpush

@section('content')
<div class="artist-profile-page">

    {{-- HERO COVER --}}
    <section class="artist-cover">
        @if ($coverPhoto && $coverUrl = $artworkImageUrl($coverPhoto))
            <img
                class="artist-cover-image"
                src="{{ $coverUrl }}"
                alt="{{ $artistName }}'s cover photo"
            >
        @endif

        <div class="artist-cover-overlay"></div>

        <div class="artist-cover-caption">
            <span class="artist-cover-dot"></span>
            ARTFY · THE ARTIST COLLECTION
        </div>
    </section>

    {{-- ARTIST INTRODUCTION --}}
    <section class="artist-profile-intro">

        <div class="artist-avatar">
            @if ($profilePhoto && $profileUrl = $artworkImageUrl($profilePhoto))
                <img
                    src="{{ $profileUrl }}"
                    alt="{{ $artistName }}"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';"
                >

                <span class="artist-avatar-fallback" style="display:none">
                    {{ $initial }}
                </span>
            @else
                <span class="artist-avatar-fallback">
                    {{ $initial }}
                </span>
            @endif
        </div>

        <div class="artist-heading">
            <p class="artist-label">MEET THE ARTIST</p>
            <h1>{{ $artistName }}</h1>

            <div class="artist-meta">
                @if ($discipline)
                    <span>
                        <span aria-hidden="true">✦</span>
                        {{ $discipline }}
                    </span>
                @endif

                @if ($location)
                    <span>
                        <span aria-hidden="true">⌖</span>
                        {{ $location }}
                    </span>
                @endif
            </div>
        </div>

        <div class="artist-profile-actions">
            @if ($isAdminPreview)
                @if (\Illuminate\Support\Facades\Route::has('admin.artists.index'))
                    <a
                        class="artist-button artist-button-outline"
                        href="{{ route('admin.artists.index') }}"
                    >
                        ← Back to Artists
                    </a>
                @endif

            @elseif (
                ($canManageArtistProfile ?? false)
                && isset($user)
                && $user->isArtist()
            )
                @if (\Illuminate\Support\Facades\Route::has('artist-profile.edit'))
                    <a
                        class="artist-button artist-button-outline"
                        href="{{ route('artist-profile.edit') }}"
                    >
                        Edit Profile
                    </a>
                @endif

                @if (\Illuminate\Support\Facades\Route::has('artist.artworks.create'))
                    <a
                        class="artist-button artist-button-primary"
                        href="{{ route('artist.artworks.create') }}"
                    >
                        + Add Artwork
                    </a>
                @endif
            @endif
        </div>

    </section>

    {{-- ABOUT --}}
    <section class="artist-story">
        <div class="artist-story-heading">
            <p class="artist-eyebrow">THE PERSON BEHIND THE ART</p>
            <h2>Every artist has<br>a story to tell.</h2>
            <span class="artist-heading-decoration" aria-hidden="true">✳</span>
        </div>

        <div class="artist-story-copy">
            @if (filled($bio))
                <p class="artist-bio">{{ $bio }}</p>
            @else
                <p class="artist-bio">
                    Every artwork begins with an idea. Discover this artist's
                    collection and follow their creative journey.
                </p>
            @endif

            @if ($safePortfolioUrl)
                <a
                    class="artist-profile-link"
                    href="{{ $safePortfolioUrl }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Visit External Portfolio
                    <span aria-hidden="true">↗</span>
                </a>
            @endif
        </div>
    </section>

    {{-- STATISTICS --}}
    <section class="artist-stats" aria-label="Artist portfolio statistics">
        <div class="artist-stat">
            <span class="artist-stat-icon" aria-hidden="true">✳</span>
            <strong>{{ $totalArtworks }}</strong>
            <span>Total Artworks</span>
        </div>

        <div class="artist-stat">
            <span class="artist-stat-icon" aria-hidden="true">◇</span>
            <strong>{{ $totalAvailable }}</strong>
            <span>Available</span>
        </div>

        <div class="artist-stat">
            <span class="artist-stat-icon" aria-hidden="true">✓</span>
            <strong>{{ $totalSold }}</strong>
            <span>Sold</span>
        </div>

        <div class="artist-stat">
            <span class="artist-stat-icon" aria-hidden="true">⌁</span>
            <strong>{{ $otherArtworks->count() }}</strong>
            <span>Other Status</span>
        </div>
    </section>

    {{-- PORTFOLIO --}}
    <section class="artist-portfolio" id="artist-portfolio">

        <div class="artist-portfolio-header">
            <div>
                <p class="artist-eyebrow">A CURATED COLLECTION</p>
                <h2>Art, with a story.</h2>
                <p class="artist-portfolio-subtitle">
                    Explore original creations by {{ $artistName }}.
                </p>
            </div>

            <span class="artist-portfolio-mark" aria-hidden="true">ART.</span>
        </div>

        <nav class="artist-portfolio-tabs" aria-label="Portfolio sections">
            <a href="#available-artworks">
                Available <span>{{ $totalAvailable }}</span>
            </a>

            <a href="#sold-artworks">
                Sold <span>{{ $totalSold }}</span>
            </a>

            @if ($otherArtworks->isNotEmpty())
                <a href="#other-artworks">
                    Other <span>{{ $otherArtworks->count() }}</span>
                </a>
            @endif
        </nav>

        {{-- AVAILABLE ARTWORKS --}}
        <section class="artist-artwork-section" id="available-artworks">
            <div class="artist-subsection-heading">
                <div>
                    <p class="artist-eyebrow">READY FOR A NEW HOME</p>
                    <h3>Available artworks</h3>
                </div>

                <span class="artist-section-count">
                    {{ str_pad((string) $totalAvailable, 2, '0', STR_PAD_LEFT) }}
                </span>
            </div>

            <div class="artist-artwork-grid">
                @forelse ($availableArtworks as $artwork)
                    <a
                        class="artist-artwork-card"
                        href="{{ route('artworks.show', ['slug' => 'uploaded-' . $artwork->id]) }}"
                    >
                        <div class="artist-artwork-image">
                            @if ($imageUrl = $artworkImageUrl($artwork->image ?? null))
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $artwork->title ?? 'Artwork' }}"
                                    loading="lazy"
                                >
                            @else
                                <div class="artist-artwork-image-placeholder">
                                    <span>✳</span>
                                    Artwork image unavailable
                                </div>
                            @endif

                            <span class="artist-availability-badge">Available</span>
                            <span class="artist-image-action" aria-hidden="true">↗</span>
                        </div>

                        <div class="artist-artwork-info">
                            <div>
                                <h4>{{ $artwork->title ?? 'Untitled artwork' }}</h4>

                                @if (filled($artwork->category ?? null))
                                    <p>{{ $artwork->category }}</p>
                                @endif
                            </div>

                            @if (isset($artwork->price) && $artwork->price !== null)
                                <span class="artist-artwork-price">
                                    ৳{{ number_format((float) $artwork->price, 2) }}
                                </span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="artist-empty-portfolio">
                        <span class="artist-empty-mark" aria-hidden="true">✳</span>
                        <h4>New art is on its way</h4>
                        <p>
                            There are no approved artworks available right now.
                            Check back soon to discover new creations.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- SOLD ARTWORKS --}}
        <section class="artist-artwork-section" id="sold-artworks">
            <div class="artist-subsection-heading">
                <div>
                    <p class="artist-eyebrow">A PART OF THEIR JOURNEY</p>
                    <h3>Previously sold</h3>
                </div>

                <span class="artist-section-count">
                    {{ str_pad((string) $totalSold, 2, '0', STR_PAD_LEFT) }}
                </span>
            </div>

            <div class="artist-artwork-grid">
                @forelse ($soldArtworks as $artwork)
                    <a
                        class="artist-artwork-card artist-artwork-sold"
                        href="{{ route('artworks.show', ['slug' => 'uploaded-' . $artwork->id]) }}"
                    >
                        <div class="artist-artwork-image">
                            @if ($imageUrl = $artworkImageUrl($artwork->image ?? null))
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $artwork->title ?? 'Sold artwork' }}"
                                    loading="lazy"
                                >
                            @else
                                <div class="artist-artwork-image-placeholder">
                                    Artwork image unavailable
                                </div>
                            @endif

                            <span class="artist-sold-badge">Sold</span>
                            <span class="artist-image-action" aria-hidden="true">↗</span>
                        </div>

                        <div class="artist-artwork-info">
                            <div>
                                <h4>{{ $artwork->title ?? 'Untitled artwork' }}</h4>

                                @if (filled($artwork->category ?? null))
                                    <p>{{ $artwork->category }}</p>
                                @endif
                            </div>

                            <span class="artist-other-status">Collected</span>
                        </div>
                    </a>
                @empty
                    <div class="artist-empty-portfolio">
                        <span class="artist-empty-mark" aria-hidden="true">✳</span>
                        <h4>No sold artworks yet</h4>
                        <p>Previously sold pieces will appear here.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- OTHER ARTWORKS --}}
        @if ($otherArtworks->isNotEmpty())
            <section class="artist-artwork-section" id="other-artworks">
                <div class="artist-subsection-heading">
                    <div>
                        <p class="artist-eyebrow">MORE FROM THE STUDIO</p>
                        <h3>Other artworks</h3>
                    </div>

                    <span class="artist-section-count">
                        {{ str_pad((string) $otherArtworks->count(), 2, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <div class="artist-artwork-grid">
                    @foreach ($otherArtworks as $artwork)
                        <div class="artist-artwork-card">
                            <div class="artist-artwork-image">
                                @if ($imageUrl = $artworkImageUrl($artwork->image ?? null))
                                    <img
                                        src="{{ $imageUrl }}"
                                        alt="{{ $artwork->title ?? 'Artwork' }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="artist-artwork-image-placeholder">
                                        Artwork image unavailable
                                    </div>
                                @endif

                                <span class="artist-availability-badge">
                                    {{ ucfirst(str_replace('_', ' ', $artwork->moderation_status ?? $artwork->status ?? 'Pending')) }}
                                </span>
                            </div>

                            <div class="artist-artwork-info">
                                <div>
                                    <h4>{{ $artwork->title ?? 'Untitled artwork' }}</h4>

                                    @if (filled($artwork->category ?? null))
                                        <p>{{ $artwork->category }}</p>
                                    @endif
                                </div>

                                <span class="artist-other-status">Not for sale</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    </section>
</div>
@endsection
