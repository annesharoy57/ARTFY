
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
        * { box-sizing: border-box; }

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
            width: 100%;
            height: 260px;
            object-fit: cover;
            display: block;
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

        .art-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
        }

        .art-card {
            min-width: 0;
            overflow: hidden;
            background: #fffdf9;
            border: 1px solid #e9e5dc;
            border-radius: 14px;
            transition: transform .2s ease;
        }

        .art-card:hover {
            transform: translateY(-4px);
        }

        .art-image {
            display: block;
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            background: #eeeee4;
        }

        .art-image-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #72795f;
            font-size: 14px;
        }

        .art-info {
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

        .empty {
            grid-column: 1 / -1;
            padding: 38px 20px;
            border: 1px dashed #c9cbbd;
            border-radius: 14px;
            text-align: center;
            color: #72795f;
            line-height: 1.8;
        }

        .footer {
            padding: 24px;
            border-top: 1px solid #e6e3d9;
            text-align: center;
            color: #72795f;
            font-size: 12px;
        }

        @media (max-width: 700px) {
            .container { margin-top: 24px; }
            .cover { height: 190px; }
            .profile-content { padding: 0 20px 30px; }
            .art-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 460px) {
            .topbar { padding: 18px; }
            .brand { font-size: 23px; }
            .back-link { font-size: 12px; }
            .art-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}">ARTFY</a>

        <a class="back-link" href="{{ route('artists.index') }}">
            &larr; Back to artists
        </a>
    </header>

    <main class="container">
        <section class="profile">
            @if(!empty($coverPhoto))
                <img
                    class="cover"
                    src="{{ asset('storage/' . $coverPhoto) }}"
                    alt="Cover photo for {{ $artistName }}"
                >
            @else
                <div class="cover cover-placeholder"></div>
            @endif

            <div class="profile-content">
                @if(!empty($profilePhoto))
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

                @if(!empty($location))
                    <p class="location">
                        &#9906; {{ $location }}
                    </p>
                @endif

                <p class="bio">{{ $bio }}</p>

                @if(!empty($portfolioUrl))
                    @php
                        $portfolioHost = parse_url($portfolioUrl, PHP_URL_HOST);
                        $safePortfolioUrl = in_array(
                            parse_url($portfolioUrl, PHP_URL_SCHEME),
                            ['http', 'https'],
                            true
                        ) ? $portfolioUrl : null;
                    @endphp

                    @if($safePortfolioUrl)
                        <a
                            class="portfolio-link"
                            href="{{ $safePortfolioUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Visit external portfolio
                        </a>
                    @endif
                @endif
            </div>
        </section>

        <section class="portfolio">
            <div class="portfolio-heading">
                <div>
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

            <div class="art-grid">
                @forelse($artworks as $artwork)
                    <a
                        class="art-card"
                        href="{{ route('artworks.show', ['slug' => 'uploaded-' . $artwork->id]) }}"
                    >
                        @if(!empty($artwork->image))
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

                        <div class="art-info">
                            <h3>{{ $artwork->title }}</h3>

                            @if($artwork->category)
                                <p class="muted">{{ $artwork->category }}</p>
                            @endif

                            @if($artwork->price !== null)
                                <p class="price">
                                    ৳{{ number_format((float) $artwork->price, 2) }}
                                </p>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="empty">
                        <strong>No published artworks yet.</strong>
                        <p>
                            This artist's approved artworks will appear here
                            when they have published work.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    </main>

    <footer class="footer">
        Artfy · Discover art. Meet artists. Find inspiration.
    </footer>
</body>
</html>
