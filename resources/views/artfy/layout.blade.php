
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f3e8">

    {{-- Page title --}}
    @if (request()->routeIs('home'))
        <title>Artfy | Discover Art</title>
    @elseif (request()->routeIs('my-profile'))
        <title>My Profile | Artfy</title>
    @elseif (request()->routeIs('artist-profile.*'))
        <title>Artist Profile | Artfy</title>
    @elseif (request()->routeIs('artworks.*'))
        <title>Artworks | Artfy</title>
    @elseif (request()->routeIs('artists.*'))
        <title>Artists | Artfy</title>
    @elseif (request()->routeIs('exhibitions.*'))
        <title>Exhibitions | Artfy</title>
    @elseif (request()->routeIs('favorites.*'))
        <title>Favorites | Artfy</title>
    @elseif (request()->routeIs('artist.studio'))
        <title>Artist Studio | Artfy</title>
    @elseif (request()->routeIs('admin.*'))
        <title>Admin Dashboard | Artfy</title>
    @elseif (request()->routeIs('login'))
        <title>Login | Artfy</title>
    @elseif (request()->routeIs('register'))
        <title>Sign Up | Artfy</title>
    @elseif (request()->routeIs('auth.choose-role'))
        <title>Choose Your Role | Artfy</title>
    @elseif (request()->routeIs('artist.application'))
        <title>Artist Application | Artfy</title>
    @else
        <title>Artfy | Art Gallery</title>
    @endif

    <link rel="icon" type="image/svg+xml"
          href="{{ asset('artfy-logo.svg') }}?v=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet"
          href="{{ asset('css/artfy.css') }}?v=20261016-clean-navbar">
</head>

<body>

    {{-- ================= HEADER ================= --}}
    <header class="site-header">

        {{-- Brand --}}
        <a class="brand"
           href="{{ route('home') }}"
           aria-label="Artfy home">

            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" focusable="false">
                    <circle cx="16" cy="6.5" r="3.1"/>
                    <circle cx="6.5" cy="16" r="3.1"/>
                    <circle cx="16" cy="16" r="2.4"/>
                    <circle cx="25.5" cy="16" r="3.1"/>
                    <circle cx="16" cy="25.5" r="3.1"/>
                </svg>
            </span>

            <span class="brand-word">
                <span class="brand-art">Art</span><span class="brand-fy">fy</span><span class="brand-dot">.</span>
            </span>
        </a>

        {{-- Main navigation --}}
        <nav class="main-nav" aria-label="Main navigation">

            <a @class(['active' => request()->routeIs('home')])
               href="{{ route('home') }}">
                Discover
            </a>

            <a @class(['active' => request()->routeIs('artworks.*')])
               href="{{ route('artworks.index') }}">
                Artworks
            </a>

            <a @class(['active' => request()->routeIs('artists.*')])
               href="{{ route('artists.index') }}">
                Artists
            </a>

            <a @class(['active' => request()->routeIs('exhibitions.*')])
               href="{{ route('exhibitions.index') }}">
                Exhibitions
            </a>

        </nav>

        {{-- Header actions --}}
        <div class="header-actions">

            @guest

                <button type="button"
                        class="text-link auth-open-button"
                        data-auth-mode="login">
                    Login
                </button>

                <button type="button"
                        class="button button-dark button-small auth-open-button"
                        data-auth-mode="register">
                    Sign Up
                </button>

            @else

                {{-- Artists and art lovers get one My Profile link.
                     Admins do not see this regular-user link. --}}
                @unless (auth()->user()->isAdmin())
                    <a
                        href="{{ route('my-profile') }}"
                        @class([
                            'text-link',
                            'active' => request()->routeIs('my-profile')
                                || request()->routeIs('artist-profile.*'),
                        ])
                    >
                        My Profile
                    </a>
                @endunless

                <form action="{{ route('logout') }}"
                      method="POST"
                      class="logout-form">
                    @csrf

                    <button type="submit"
                            class="button button-dark button-small">
                        Logout
                    </button>
                </form>

            @endguest

        </div>
    </header>

    {{-- ================= MAIN CONTENT ================= --}}
    <main>

        @if (session('success'))
            <div class="container">
                <p class="success-message" role="status">
                    {{ session('success') }}
                </p>
            </div>
        @endif

        @if (session('error'))
            <div class="container">
                <p class="error-message" role="alert">
                    {{ session('error') }}
                </p>
            </div>
        @endif

        @yield('content')

    </main>

    {{-- ================= FOOTER ================= --}}
    <footer class="site-footer">

        <a class="brand footer-brand"
           href="{{ route('home') }}"
           aria-label="Artfy home">

            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" focusable="false">
                    <circle cx="16" cy="6.5" r="3.1"/>
                    <circle cx="6.5" cy="16" r="3.1"/>
                    <circle cx="16" cy="16" r="2.4"/>
                    <circle cx="25.5" cy="16" r="3.1"/>
                    <circle cx="16" cy="25.5" r="3.1"/>
                </svg>
            </span>

            <span class="brand-word">
                <span class="brand-art">Art</span><span class="brand-fy">fy</span><span class="brand-dot">.</span>
            </span>
        </a>

        <p>
            A space for art, artists, and the people who love both.
        </p>

        <div class="footer-links">
            <a href="{{ route('artworks.index') }}">Explore art</a>
            <a href="{{ route('artists.index') }}">Discover artists</a>
            <a href="{{ route('exhibitions.index') }}">Exhibitions</a>
            <a href="{{ route('favorites.index') }}">Favorites</a>

            @auth
                @if (auth()->user()->isArtist())
                    <a href="{{ route('artist.studio') }}">Artist Studio</a>
                @endif

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
            @endauth
        </div>

        <span class="copyright">
            © {{ date('Y') }} Artfy
        </span>

    </footer>

    {{-- ================= LOGIN / SIGN-UP ROLE MODAL ================= --}}
    <div class="auth-modal"
         id="authModal"
         aria-hidden="true"
         inert>

        <div class="auth-modal-backdrop" data-auth-close></div>

        <section class="auth-modal-panel"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="authModalTitle"
                 aria-describedby="authModalDescription">

            <button type="button"
                    class="auth-modal-close"
                    aria-label="Close dialog"
                    data-auth-close>
                &times;
            </button>

            <div class="auth-modal-heading">

                <span class="auth-modal-eyebrow">
                    THE ART COMMUNITY
                </span>

                <h2 id="authModalTitle">Welcome to Artfy</h2>

                <p id="authModalDescription">
                    How would you like to continue?
                </p>

            </div>

            <div class="auth-role-options">

                {{-- Artist option --}}
                <a class="auth-role-card"
                   id="artistAuthLink"
                   href="{{ route('login', ['role' => 'artist']) }}">

                    <div class="auth-role-art artist-role-art">
                        <span>01</span>
                        <span class="auth-role-symbol" aria-hidden="true">✳</span>
                    </div>

                    <div class="auth-role-info">

                        <span class="auth-role-kicker">
                            CREATE &amp; EXHIBIT
                        </span>

                        <h3>I'm an Artist</h3>

                        <p>
                            Showcase your creations, build your portfolio,
                            and apply for artist membership.
                        </p>

                        <span class="auth-role-link" id="artistAuthAction">
                            Continue as Artist
                            <span aria-hidden="true">→</span>
                        </span>

                    </div>
                </a>

                {{-- Art Lover option --}}
                <a class="auth-role-card"
                   id="loverAuthLink"
                   href="{{ route('login', ['role' => 'art_lover']) }}">

                    <div class="auth-role-art lover-role-art">
                        <span>02</span>
                        <span class="auth-role-symbol" aria-hidden="true">◈</span>
                    </div>

                    <div class="auth-role-info">

                        <span class="auth-role-kicker">
                            DISCOVER &amp; COLLECT
                        </span>

                        <h3>I'm an Art Lover</h3>

                        <p>
                            Discover beautiful art, follow artists,
                            save favorites, and collect your favorite pieces.
                        </p>

                        <span class="auth-role-link" id="loverAuthAction">
                            Continue as Art Lover
                            <span aria-hidden="true">→</span>
                        </span>

                    </div>
                </a>

            </div>

            <p class="auth-modal-note">
                <span aria-hidden="true">✦</span>
                Every great collection begins with a discovery.
            </p>

        </section>
    </div>

    {{-- ================= MODAL JAVASCRIPT ================= --}}
    <script>
        (() => {
            const modal = document.getElementById('authModal');

            if (!modal) {
                return;
            }

            const title = document.getElementById('authModalTitle');
            const description = document.getElementById('authModalDescription');

            const artistLink = document.getElementById('artistAuthLink');
            const loverLink = document.getElementById('loverAuthLink');

            const artistAction = document.getElementById('artistAuthAction');
            const loverAction = document.getElementById('loverAuthAction');

            let previousFocus = null;

            // Laravel generates the authentication URLs.
            const urls = {
                login: {
                    artist: @json(route('login', ['role' => 'artist'])),
                    art_lover: @json(route('login', ['role' => 'art_lover']))
                },
                register: {
                    artist: @json(route('register', ['role' => 'artist'])),
                    art_lover: @json(route('register', ['role' => 'art_lover']))
                }
            };

            function openModal(mode) {
                previousFocus = document.activeElement;

                const isRegister = mode === 'register';
                const page = isRegister ? 'register' : 'login';

                title.textContent = isRegister
                    ? 'Begin Your Artfy Journey'
                    : 'Welcome Back to Artfy';

                description.textContent = isRegister
                    ? 'Choose your place in our art community.'
                    : 'Choose the type of account you want to access.';

                artistLink.href = urls[page].artist;
                loverLink.href = urls[page].art_lover;

                artistAction.innerHTML = isRegister
                    ? 'Apply as an Artist <span aria-hidden="true">→</span>'
                    : 'Login as Artist <span aria-hidden="true">→</span>';

                loverAction.innerHTML = isRegister
                    ? 'Create Free Account <span aria-hidden="true">→</span>'
                    : 'Login as Art Lover <span aria-hidden="true">→</span>';

                modal.inert = false;
                modal.setAttribute('aria-hidden', 'false');
                modal.classList.add('is-open');
                document.body.classList.add('auth-modal-open');

                modal.querySelector('.auth-modal-close').focus();
            }

            function closeModal() {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                modal.inert = true;

                document.body.classList.remove('auth-modal-open');

                if (previousFocus && typeof previousFocus.focus === 'function') {
                    previousFocus.focus();
                }
            }

            // Open from Login or Sign Up.
            document.querySelectorAll('[data-auth-mode]').forEach(button => {
                button.addEventListener('click', () => {
                    openModal(button.dataset.authMode);
                });
            });

            // Close from the close button or backdrop.
            modal.querySelectorAll('[data-auth-close]').forEach(element => {
                element.addEventListener('click', closeModal);
            });

            // Close with Escape.
            document.addEventListener('keydown', event => {
                if (
                    event.key === 'Escape' &&
                    modal.classList.contains('is-open')
                ) {
                    closeModal();
                }
            });
        })();
    </script>

</body>
</html>
