<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8f6ef">

    {{-- Page title --}}
    @if (request()->routeIs('home'))
        <title>Artfy | Discover Art</title>
    @elseif (request()->routeIs('purchases.*'))
        <title>My Purchases | Artfy</title>
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
    @elseif (request()->routeIs('notifications.*'))
        <title>Notifications | Artfy</title>
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

    <link rel="icon"
          type="image/svg+xml"
          href="{{ asset('artfy-logo.svg') }}?v=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet"
          href="{{ asset('css/artfy.css') }}?v=20261017-auth-roles">

    <style>
        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .notification-bell-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            color: #414c2c;
            border: 1px solid rgba(65, 76, 44, 0.18);
            border-radius: 50%;
            text-decoration: none;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .notification-bell-link:hover {
            background: #eeece2;
            transform: translateY(-1px);
        }

        .notification-bell-icon {
            display: block;
            width: 21px;
            height: 21px;
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            border: 2px solid #f8f6ef;
            border-radius: 999px;
            background: #b74f37;
            color: #fff;
            font-family: "DM Sans", sans-serif;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
        }

        .notification-bell-link:focus-visible {
            outline: 2px solid #55613a;
            outline-offset: 3px;
        }

        .purchases-nav-link {
            white-space: nowrap;
        }

        @media (max-width: 600px) {
            .header-actions {
                gap: 10px;
            }

            .notification-bell-link {
                width: 36px;
                height: 36px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    {{-- ================= HEADER ================= --}}
    <header class="site-header">

        {{-- Artfy logo --}}
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
               href="{{ route('home') }}"
               @if (request()->routeIs('home')) aria-current="page" @endif>
                Discover
            </a>

            <a @class(['active' => request()->routeIs('artworks.*')])
               href="{{ route('artworks.index') }}"
               @if (request()->routeIs('artworks.*')) aria-current="page" @endif>
                Artworks
            </a>

            <a @class(['active' => request()->routeIs('artists.*')])
               href="{{ route('artists.index') }}"
               @if (request()->routeIs('artists.*')) aria-current="page" @endif>
                Artists
            </a>

            <a @class(['active' => request()->routeIs('exhibitions.*')])
               href="{{ route('exhibitions.index') }}"
               @if (request()->routeIs('exhibitions.*')) aria-current="page" @endif>
                Exhibitions
            </a>

            {{-- Only Art Lovers can access favorites and purchases. --}}
            @auth
                @if (auth()->user()->isArtLover())
                    <a class="{{ request()->routeIs('favorites.*') ? 'active' : '' }}"
                       href="{{ route('favorites.index') }}"
                       @if (request()->routeIs('favorites.*')) aria-current="page" @endif>
                        Favorites
                    </a>

                    <a class="purchases-nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}"
                       href="{{ route('purchases.index') }}"
                       @if (request()->routeIs('purchases.*')) aria-current="page" @endif>
                        My Purchases
                    </a>
                @endif
            @endauth
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

                {{-- Notification bell --}}
                @php
                    $unreadNotificationCount = auth()->user()
                        ->unreadNotifications()
                        ->count();
                @endphp

                <a href="{{ route('notifications.index') }}"
                   class="notification-bell-link"
                   aria-label="Notifications{{ $unreadNotificationCount > 0 ? ', ' . $unreadNotificationCount . ' unread' : '' }}"
                   title="Notifications">

                    <svg class="notification-bell-icon"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.7"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                        <path d="M10 21h4"></path>
                    </svg>

                    @if ($unreadNotificationCount > 0)
                        <span class="notification-count">
                            {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                        </span>
                    @endif
                </a>

                {{-- My Profile for non-admin users --}}
                @unless (auth()->user()->isAdmin())
                    <a href="{{ route('my-profile') }}"
                       @class([
                           'text-link',
                           'active' => request()->routeIs('my-profile')
                               || request()->routeIs('artist-profile.*'),
                       ])
                       @if (
                           request()->routeIs('my-profile')
                           || request()->routeIs('artist-profile.*')
                       )
                           aria-current="page"
                       @endif>
                        My Profile
                    </a>
                @endunless

                @if (auth()->user()->isArtist())
                    <a href="{{ route('artist.wallet') }}"
                       class="text-link {{ request()->routeIs('artist.wallet') ? 'active' : '' }}">
                        Prize Wallet
                    </a>
                @endif

                {{-- Logout --}}
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

    @if (request()->routeIs('home') && $exhibitionAd)
        <aside
            class="discover-exhibition-ad"
            data-exhibition-ad
            data-exhibition-ad-id="{{ $exhibitionAd['id'] }}"
            aria-label="Online exhibition announcement"
            hidden
        >
            <div class="discover-exhibition-ad-copy">
                <span>{{ $exhibitionAd['type'] === 'ongoing' ? 'EXHIBITION IS GOING' : 'REGISTRATION OPEN' }}</span>
                <p>
                    {{ $exhibitionAd['type'] === 'ongoing' ? 'Explore the exhibition:' : 'Coming soon:' }}
                    <strong>{{ $exhibitionAd['title'] }}</strong>
                </p>
            </div>
            <a href="{{ route('exhibitions.show', $exhibitionAd['slug']) }}">
                {{ $exhibitionAd['type'] === 'ongoing' ? 'Visit exhibition' : 'View exhibition' }}
                <span aria-hidden="true">→</span>
            </a>
            <button type="button" data-dismiss-exhibition-ad aria-label="Close exhibition announcement">
                <span aria-hidden="true">&times;</span>
            </button>
        </aside>
        <style>
            .discover-exhibition-ad{position:relative;display:flex;align-items:center;justify-content:center;gap:24px;padding:12px 56px;background:#414c2c;color:#fff}
            .discover-exhibition-ad[hidden]{display:none}
            .discover-exhibition-ad-copy>span{font-size:.7rem;font-weight:700;letter-spacing:.12em;opacity:.8}
            .discover-exhibition-ad p{margin:3px 0 0}
            .discover-exhibition-ad>a{color:#fff;font-weight:700;text-decoration:underline;text-underline-offset:3px}
            .discover-exhibition-ad>button{position:absolute;top:50%;right:16px;display:grid;width:32px;height:32px;place-items:center;transform:translateY(-50%);border:1px solid rgba(255,255,255,.55);border-radius:50%;background:transparent;color:inherit;font-size:1.35rem;line-height:1;cursor:pointer}
            .discover-exhibition-ad>button:focus-visible,.discover-exhibition-ad>a:focus-visible{outline:2px solid #fff;outline-offset:3px}
            @media(max-width:600px){.discover-exhibition-ad{align-items:flex-start;flex-direction:column;gap:8px;padding:12px 54px 12px 16px}.discover-exhibition-ad>button{right:12px}}
        </style>
        <script>
            (() => {
                const banner = document.querySelector('[data-exhibition-ad]');
                if (!banner) return;

                const storageKey = `artfy-dismissed-exhibition-${banner.dataset.exhibitionAdId}`;
                try {
                    if (sessionStorage.getItem(storageKey) === '1') return;
                } catch (error) {
                    banner.hidden = false;
                }

                banner.hidden = false;
                banner.querySelector('[data-dismiss-exhibition-ad]').addEventListener('click', () => {
                    try {
                        sessionStorage.setItem(storageKey, '1');
                    } catch (error) {
                        // Keep the close button functional when browser storage is unavailable.
                    }
                    banner.hidden = true;
                });
            })();
        </script>
    @endif

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
            @auth
                @if (auth()->user()->isArtLover())
                    <a href="{{ route('favorites.index') }}">Favorites</a>
                @endif
            @else
                <a href="{{ route('login') }}">Favorites</a>
            @endauth

            @auth
                {{-- Purchases are visible to Art Lovers only --}}
                @if (auth()->user()->isArtLover())
                    <a href="{{ route('purchases.index') }}">My Purchases</a>
                @endif

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
                ×
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
                {{-- Artist --}}
                <a class="auth-role-card"
                   id="artistAuthLink"
                   href="{{ route('login', ['role' => 'artist']) }}">

                    <div class="auth-role-art artist-role-art">
                        <span>01</span>
                        <span class="auth-role-symbol" aria-hidden="true">✳</span>
                    </div>

                    <div class="auth-role-info">
                        <span class="auth-role-kicker">CREATE &amp; EXHIBIT</span>
                        <h3>I'm an Artist</h3>
                        <p>
                            Showcase your creations, build your portfolio,
                            and apply for artist membership.
                        </p>
                        <span class="auth-role-link" id="artistAuthAction">
                            Login as Artist <span aria-hidden="true">→</span>
                        </span>
                    </div>
                </a>

                {{-- Art Lover --}}
                <a class="auth-role-card"
                   id="loverAuthLink"
                   href="{{ route('login', ['role' => 'art_lover']) }}">

                    <div class="auth-role-art lover-role-art">
                        <span>02</span>
                        <span class="auth-role-symbol" aria-hidden="true">◈</span>
                    </div>

                    <div class="auth-role-info">
                        <span class="auth-role-kicker">DISCOVER &amp; COLLECT</span>
                        <h3>I'm an Art Lover</h3>
                        <p>
                            Discover beautiful art, follow artists,
                            save favorites, and collect your favorite pieces.
                        </p>
                        <span class="auth-role-link" id="loverAuthAction">
                            Login as Art Lover <span aria-hidden="true">→</span>
                        </span>
                    </div>
                </a>

                {{-- Admin: login only --}}
                <a class="auth-role-card admin-role-card"
                   id="adminAuthLink"
                   href="{{ route('login', ['role' => 'admin']) }}"
                   hidden>

                    <div class="auth-role-art admin-role-art">
                        <span>03</span>
                        <span class="auth-role-symbol" aria-hidden="true">✦</span>
                    </div>

                    <div class="auth-role-info">
                        <span class="auth-role-kicker">ADMINISTRATION</span>
                        <h3>I'm an Admin</h3>
                        <p>
                            Access the administration area using
                            your authorized administrator account.
                        </p>
                        <span class="auth-role-link" id="adminAuthAction">
                            Login as Admin <span aria-hidden="true">→</span>
                        </span>
                    </div>
                </a>

                {{-- Co-admin: login only --}}
                <a class="auth-role-card admin-role-card"
                   id="coAdminAuthLink"
                   href="{{ route('login', ['role' => 'co_admin']) }}"
                   hidden>

                    <div class="auth-role-art admin-role-art">
                        <span>04</span>
                        <span class="auth-role-symbol" aria-hidden="true">◇</span>
                    </div>

                    <div class="auth-role-info">
                        <span class="auth-role-kicker">EXHIBITION JUDGING</span>
                        <h3>I'm a Co-admin</h3>
                        <p>
                            Review exhibition artwork and score participants
                            during the visiting period.
                        </p>
                        <span class="auth-role-link" id="coAdminAuthAction">
                            Login as Co-admin <span aria-hidden="true">→</span>
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
            const adminLink = document.getElementById('adminAuthLink');
            const coAdminLink = document.getElementById('coAdminAuthLink');

            const artistAction = document.getElementById('artistAuthAction');
            const loverAction = document.getElementById('loverAuthAction');
            const adminAction = document.getElementById('adminAuthAction');
            const coAdminAction = document.getElementById('coAdminAuthAction');

            let previousFocus = null;

            const urls = {
                login: {
                    artist: @json(route('login', ['role' => 'artist'])),
                    art_lover: @json(route('login', ['role' => 'art_lover'])),
                    admin: @json(route('login', ['role' => 'admin'])),
                    co_admin: @json(route('login', ['role' => 'co_admin']))
                },
                register: {
                    artist: @json(route('register', ['role' => 'artist'])),
                    art_lover: @json(route('register', ['role' => 'art_lover']))
                }
            };

            function setActionLabel(element, label) {
                element.firstChild.textContent = label + ' ';
            }

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

                adminLink.hidden = isRegister;
                coAdminLink.hidden = isRegister;

                if (!isRegister) {
                    adminLink.href = urls.login.admin;
                    coAdminLink.href = urls.login.co_admin;
                }

                setActionLabel(
                    artistAction,
                    isRegister ? 'Apply as an Artist' : 'Login as Artist'
                );

                setActionLabel(
                    loverAction,
                    isRegister ? 'Create Free Account' : 'Login as Art Lover'
                );

                setActionLabel(adminAction, 'Login as Admin');
                setActionLabel(coAdminAction, 'Login as Co-admin');

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

                if (
                    previousFocus &&
                    typeof previousFocus.focus === 'function'
                ) {
                    previousFocus.focus();
                }
            }

            document.querySelectorAll('[data-auth-mode]').forEach(button => {
                button.addEventListener('click', () => {
                    openModal(button.dataset.authMode);
                });
            });

            modal.querySelectorAll('[data-auth-close]').forEach(element => {
                element.addEventListener('click', closeModal);
            });

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

    @stack('scripts')
</body>
</html>
