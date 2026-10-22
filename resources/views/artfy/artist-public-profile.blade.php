<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $artistName }} | Artfy</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f8f6f2;
            color: #414c2c;
            font-family: 'DM Sans', sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .topbar {
            max-width: 1120px;
            margin: auto;
            padding: 22px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #e6e3d9;
        }

        .brand {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .back-link {
            color: #55613a;
            font-size: 14px;
        }

        .container {
            max-width: 1050px;
            margin: 38px auto;
            padding: 0 24px 60px;
        }

        .cover {
            display: block;
            width: 100%;
            height: 260px;
            object-fit: cover;
            background: #dfe2d2;
            border-radius: 18px 18px 0 0;
        }

        .cover-placeholder {
            background: linear-gradient(135deg, #dfe2d2, #eeeee4, #c9cbbd);
        }

        .profile {
            background: #eeeee4;
            border-radius: 18px;
            overflow: hidden;
        }

        .profile-content {
            padding: 0 36px 38px;
            text-align: center;
        }

        .avatar {
            width: 136px;
            height: 136px;
            margin: -68px auto 20px;
            position: relative;
            display: block;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #eeeee4;
            background: #dfe2d2;
        }

        .avatar-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #55613a;
            font-family: 'Playfair Display', serif;
            font-size: 48px;
        }

        .eyebrow {
            margin: 0 0 12px;
            color: #72795f;
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
            font-weight: 500;
        }

        h1 {
            margin: 8px 0;
            font-size: clamp(32px, 5vw, 46px);
            overflow-wrap: anywhere;
        }

        .discipline {
            margin: 0 0 18px;
            color: #72795f;
        }

        .location {
            margin: 0 0 18px;
            font-size: 14px;
            color: #72795f;
        }

        .bio {
            max-width: 680px;
            margin: 0 auto;
            line-height: 1.9;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .portfolio-link {
            display: inline-block;
            margin-top: 22px;
            padding: 11px 18px;
            border: 1px solid #55613a;
            border-radius: 8px;
            color: #414c2c;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .portfolio-link:hover {
            background: #55613a;
            color: white;
        }

        .portfolio {
            margin-top: 48px;
        }

        .portfolio-heading {
            display: flex;
            justify-content: space-between;
            align-items: end;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 22px;
        }

        .portfolio-heading h2 {
            margin: 0 0 8px;
            font-size: 32px;
        }

        .muted {
            color: #72795f;
        }

        .section-count {
            color: #72795f;
            font-size: 13px;
        }

        .artwork-section + .artwork-section {
            margin-top: 46px;
            padding-top: 32px;
            border-top: 1px solid #e6e3d9;
        }

        .art-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
            align-items: stretch;
        }

        .art-card {
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow: hidden;
            background: #fffdf9;
            border: 1px solid #e9e5dc;
            border-radius: 14px;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .art-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 22px rgba(65, 76, 44, 0.09);
        }

        /*
         * All artwork images use the same display area.
         * "contain" shows the entire image without cropping,
         * stretching, or zooming it.
         */
        .art-image-wrap {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 260px;
            padding: 10px;
            overflow: hidden;
            background: #eeeee4;
        }

        .art-image {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            background: transparent;
        }

        .art-image-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #72795f;
            font-size: 14px;
            text-align: center;
        }

        .art-status {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 1;
            display: inline-block;
            padding: 7px 11px;
            border-radius: 6px;
            font-family: 'DM Sans', sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .status-available {
            background: #e8eddf;
            color: #414c2c;
            border: 1px solid #cbd4bd;
        }

        .status-sold {
            background: #414c2c;
            color: #fffdf9;
            border: 1px solid #414c2c;
        }

        .sold-card {
            border-color: #c9cbbd;
        }

        /* Do not apply filters or zoom to sold artwork images. */
        .sold-card .art-image {
            filter: none;
        }

        .art-info {
            flex: 1;
            padding: 16px;
        }

        .art-info h3 {
            margin: 0 0 8px;
            font-size: 21px;
            overflow-wrap: anywhere;
        }

        .art-info p {
            overflow-wrap: anywhere;
        }

        .price {
            margin: 12px 0 0;
            color: #55613a;
            font-weight: 700;
        }

        .sold-label {
            margin: 12px 0 0;
            color: #72795f;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .empty {
            grid-column: 1 / -1;
            padding: 38px 20px;
            border: 1px dashed #c9cbbd;
            border-radius: 14px;
            text-align: center;
            color: #72795f;
            line-height: 1.8;
        }

        .empty strong {
            color: #414c2c;
        }

        .empty p {
            margin-bottom: 0;
        }

        .footer {
            padding: 24px;
            border-top: 1px solid #e6e3d9;
            text-align: center;
            color: #72795f;
            font-size: 12px;
        }

        @media (max-width: 700px) {
            .container {
                margin-top: 24px;
            }

            .cover {
                height: 190px;
            }

            .profile-content {
                padding: 0 20px 30px;
            }

            .art-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
            }

            .art-image-wrap {
                height: 220px;
            }

            .portfolio-heading h2 {
                font-size: 28px;
            }

            .artwork-section + .artwork-section {
                margin-top: 34px;
                padding-top: 26px;
            }
        }

        @media (max-width: 460px) {
            .topbar {
                padding: 18px;
            }

            .brand {
                font-size: 23px;
            }

            .back-link {
                font-size: 12px;
            }

            .container {
                padding-right: 16px;
                padding-left: 16px;
            }

            .art-grid {
                grid-template-columns: 1fr;
            }

            .art-image-wrap {
                height: 260px;
            }

            .portfolio-heading h2 {
                font-size: 26px;
            }
        }
    </style>
</head>

<body>
    @php
        $availableArtworks = $artworks->where('status', 'available');
        $soldArtworks = $artworks->where('status', 'sold');
    @endphp

    <header class="topbar">
        <a class="brand" href="{{ route('home') }}">ARTFY</a>

        <a class="back-link" href="{{ route('artists.index') }}">
            &larr; Back to artists
        </a>
    </header>

    <main class="container">
        {{-- ARTIST PROFILE --}}
        <section class="profile">
            @if (!empty($coverPhoto))
                <img
                    class="cover"
                    src="{{ asset('storage/' . $coverPhoto) }}"
                    alt="Cover photo for {{ $artistName }}"
                >
            @else
                <div class="cover cover-placeholder"></div>
            @endif

            <div class="profile-content">
                @if (!empty($profilePhoto))
                    <img
                        class="avatar"
                        src="{{ asset('storage/' . $profilePhoto) }}"
                        alt="{{ $artistName }}"
                    >
                @else
                    <div class="avatar avatar-placeholder">
                        {{ strtoupper(mb_substr($artistName, 0, 1)) }}
                    </div>
                @endif

                <p class="eyebrow">ARTFY · ARTIST PROFILE</p>

                <h1>{{ $artistName }}</h1>

                <p class="discipline">{{ $discipline }}</p>

                @if (!empty($location))
                    <p class="location">
                        &#9906; {{ $location }}
                    </p>
                @endif

                <p class="bio">{{ $bio }}</p>

                @if (!empty($portfolioUrl))
                    @php
                        $portfolioScheme = parse_url(
                            $portfolioUrl,
                            PHP_URL_SCHEME
                        );

                        $safePortfolioUrl = in_array(
                            strtolower((string) $portfolioScheme),
                            ['http', 'https'],
                            true
                        ) ? $portfolioUrl : null;
                    @endphp

                    @if ($safePortfolioUrl)
                        <a
                            class="portfolio-link"
                            href="{{ $safePortfolioUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Visit external portfolio ↗
                        </a>
                    @endif
                @endif
            </div>
        </section>

        {{-- ARTIST PORTFOLIO --}}
        <section class="portfolio" id="artist-portfolio">
            <div class="portfolio-heading">
                <div>
                    <p class="eyebrow">EXPLORE THE COLLECTION</p>
                    <h2>Artist Portfolio</h2>
                    <p class="muted">
                        Original work by {{ $artistName }}
                    </p>
                </div>

                <span class="muted">
                    {{ $artworks->count() }}
                    {{ $artworks->count() === 1 ? 'artwork' : 'artworks' }}
                </span>
            </div>

            {{-- AVAILABLE ARTWORKS --}}
            <section class="artwork-section" id="available-artworks">
                <div class="portfolio-heading">
                    <div>
                        <h2>Available Artworks</h2>
                        <p class="muted">
                            Discover pieces available to purchase.
                        </p>
                    </div>

                    <span class="section-count">
                        {{ $availableArtworks->count() }}
                        {{ $availableArtworks->count() === 1 ? 'available artwork' : 'available artworks' }}
                    </span>
                </div>

                <div class="art-grid">
                    @forelse ($availableArtworks as $artwork)
                        <a
                            class="art-card"
                            href="{{ route('artworks.show', ['slug' => 'uploaded-' . $artwork->id]) }}"
                        >
                            <div class="art-image-wrap">
                                @if (!empty($artwork->image))
                                    <img
                                        class="art-image"
                                        src="{{ asset('storage/' . $artwork->image) }}"
                                        alt="{{ $artwork->title }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="art-image art-image-placeholder">
                                        Artwork image unavailable
                                    </div>
                                @endif

                                <span class="art-status status-available">
                                    Available
                                </span>
                            </div>

                            <div class="art-info">
                                <h3>{{ $artwork->title }}</h3>

                                @if ($artwork->category)
                                    <p class="muted">{{ $artwork->category }}</p>
                                @endif

                                @if ($artwork->price !== null)
                                    <p class="price">
                                        ৳{{ number_format((float) $artwork->price, 2) }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="empty">
                            <strong>No available artworks right now.</strong>
                            <p>Check back later for new work from this artist.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- SOLD ARTWORKS --}}
            <section class="artwork-section" id="sold-artworks">
                <div class="portfolio-heading">
                    <div>
                        <h2>Sold Out</h2>
                        <p class="muted">
                            Previously available pieces from this artist.
                        </p>
                    </div>

                    <span class="section-count">
                        {{ $soldArtworks->count() }}
                        {{ $soldArtworks->count() === 1 ? 'sold artwork' : 'sold artworks' }}
                    </span>
                </div>

                <div class="art-grid">
                    @forelse ($soldArtworks as $artwork)
                        <a
                            class="art-card sold-card"
                            href="{{ route('artworks.show', ['slug' => 'uploaded-' . $artwork->id]) }}"
                        >
                            <div class="art-image-wrap">
                                @if (!empty($artwork->image))
                                    <img
                                        class="art-image"
                                        src="{{ asset('storage/' . $artwork->image) }}"
                                        alt="{{ $artwork->title }}"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="art-image art-image-placeholder">
                                        Artwork image unavailable
                                    </div>
                                @endif

                                <span class="art-status status-sold">
                                    Sold Out
                                </span>
                            </div>

                            <div class="art-info">
                                <h3>{{ $artwork->title }}</h3>

                                @if ($artwork->category)
                                    <p class="muted">{{ $artwork->category }}</p>
                                @endif

                                <p class="sold-label">Sold</p>
                            </div>
                        </a>
                    @empty
                        <div class="empty">
                            <strong>No sold artworks yet.</strong>
                            <p>
                                Sold pieces will appear here after their status
                                is updated in the database.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        </section>
    </main>

    <footer class="footer">
        Artfy · Discover art. Meet artists. Find inspiration.
    </footer>
</body>
</html>
