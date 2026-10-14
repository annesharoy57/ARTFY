<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8f6f2">
    @if (request()->routeIs('home'))
        <title>Artfy</title>
    @elseif (request()->routeIs('artworks.*'))
        <title>Artworks</title>
    @elseif (request()->routeIs('artists.*'))
        <title>Artists</title>
    @elseif (request()->routeIs('exhibitions.*'))
        <title>Exhibitions</title>
    @elseif (request()->routeIs('favorites.*'))
        <title>Favorites</title>
    @elseif (request()->routeIs('artist.studio'))
        <title>Artist Studio</title>
    @elseif (request()->routeIs('admin.*'))
        <title>Admin Dashboard</title>
    @elseif (request()->routeIs('login'))
        <title>Login</title>
    @elseif (request()->routeIs('register'))
        <title>Sign Up</title>
    @else
        <title>Artfy</title>
    @endif
    <link rel="icon" type="image/svg+xml" href="{{ asset('artfy-logo.svg') }}?v=1">
    <link rel="shortcut icon" href="{{ asset('artfy-logo.svg') }}?v=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/artfy.css') }}?v=20261014-artfy-brand-2">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('home') }}" aria-label="Artfy home">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" focusable="false">
                    <circle cx="16" cy="6.5" r="3.1"/>
                    <circle cx="6.5" cy="16" r="3.1"/>
                    <circle cx="16" cy="16" r="2.4"/>
                    <circle cx="25.5" cy="16" r="3.1"/>
                    <circle cx="16" cy="25.5" r="3.1"/>
                </svg>
            </span>
            <span class="brand-word"><span class="brand-art">Art</span><span class="brand-fy">fy</span><span class="brand-dot">.</span></span>
        </a>
        <nav class="main-nav" aria-label="Main navigation">
            <a @class(['active' => request()->routeIs('home')]) href="{{ route('home') }}">Home</a>
            <a @class(['active' => request()->routeIs('artworks.*')]) href="{{ route('artworks.index') }}">Artworks</a>
            <a @class(['active' => request()->routeIs('artists.*')]) href="{{ route('artists.index') }}">Artists</a>
            <a @class(['active' => request()->routeIs('exhibitions.*')]) href="{{ route('exhibitions.index') }}">Exhibitions</a>
        </nav>
        <div class="header-actions">
            <a class="text-link" href="{{ route('login') }}">Login</a>
            <a class="button button-dark button-small" href="{{ route('register') }}">Sign Up</a>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <a class="brand footer-brand" href="{{ route('home') }}" aria-label="Artfy home">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" focusable="false">
                    <circle cx="16" cy="6.5" r="3.1"/>
                    <circle cx="6.5" cy="16" r="3.1"/>
                    <circle cx="16" cy="16" r="2.4"/>
                    <circle cx="25.5" cy="16" r="3.1"/>
                    <circle cx="16" cy="25.5" r="3.1"/>
                </svg>
            </span>
            <span class="brand-word"><span class="brand-art">Art</span><span class="brand-fy">fy</span><span class="brand-dot">.</span></span>
        </a>
        <p>A space for art, artists, and the people who love both.</p>
        <div class="footer-links">
            <a href="{{ route('artworks.index') }}">Explore art</a>
            <a href="{{ route('artists.index') }}">Discover artists</a>
            <a href="{{ route('exhibitions.index') }}">Exhibitions</a>
            <a href="{{ route('favorites.index') }}">Favorites</a>
            <a href="{{ route('artist.studio') }}">Artist studio</a>
            <a href="{{ route('admin.dashboard') }}">Admin</a>
        </div>
        <span class="copyright">© {{ date('Y') }} Artfy</span>
    </footer>
</body>
</html>
