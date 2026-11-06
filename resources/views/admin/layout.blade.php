<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Artfy Administration')</title>

    {{-- Artfy browser tab icon (favicon) --}}
    <link rel="icon"
          type="image/svg+xml"
          href="{{ asset('artfy-logo.svg') }}?v=2">

    <style>
        :root {
            --paper: #f8f6f2;
            --cream: #efede4;
            --olive: #55613a;
            --dark-olive: #414c2c;
            --text: #28291f;
            --muted: #77786e;
            --white: #ffffff;
            --border: #e5e1d7;
            --success: #e5f3e5;
            --warning: #fff1cf;
            --danger: #fbe8e6;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: var(--paper);
            color: var(--text);
        }

        a {
            color: var(--olive);
        }

        /* =========================================
           SHARED ADMIN LAYOUT
        ========================================= */

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Shared sidebar for every admin page */

        .sidebar {
            width: 258px;
            flex: 0 0 258px;
            min-height: 100vh;
            padding: 30px 20px;
            background: var(--dark-olive);
            color: var(--white);
            display: flex;
            flex-direction: column;
        }

        .brand {
            display: block;
            color: var(--white);
            font-family: Georgia, serif;
            font-size: 30px;
            font-weight: bold;
            letter-spacing: 2px;
            text-decoration: none;
            margin: 0 10px 8px;
        }

        .brand:hover {
            color: var(--white);
            opacity: .9;
        }

        .side-note {
            color: #d6dacb;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 10px 32px;
        }

        .sidebar nav {
            display: flex;
            flex-direction: column;
        }

        .nav-label {
            color: #c9cebb;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 18px 10px 10px;
        }

        .nav-link {
            display: block;
            width: 100%;
            color: #f6f5ef;
            text-decoration: none;
            padding: 12px 13px;
            margin: 3px 0;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            font-family: inherit;
            font-size: 14px;
            line-height: 1.4;
            text-align: left;
            transition:
                background .2s ease,
                color .2s ease,
                border-color .2s ease;
        }

        .nav-link:hover {
            color: var(--white);
            background: #626e47;
        }

        .nav-link.active {
            color: var(--white);
            background: var(--olive);
            border-color: rgba(255, 255, 255, .08);
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 28px;
        }

        .nav-link.back-link {
            margin-top: 10px;
            border: 1px solid #899174;
        }

        .nav-link.back-link:hover {
            background: var(--olive);
            border-color: #aab296;
        }

        .logout-form {
            margin: 0;
        }

        .logout-button {
            cursor: pointer;
        }

        .logout-button:hover {
            background: #626e47;
        }

        /* =========================================
           MAIN CONTENT
        ========================================= */

        .main {
            flex: 1;
            min-width: 0;
            background: var(--paper);
        }

        /*
         * Transparent header:
         * No white background and no bottom border.
         * The notification bell remains visible.
         */

        .topbar {
            min-height: 78px;
            padding: 18px 4%;
            background: transparent;
            border: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
        }

        .topbar-label {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .admin-label {
            color: var(--dark-olive);
            background: var(--cream);
            padding: 9px 13px;
            border-radius: 30px;
            font-size: 12px;
            white-space: nowrap;
        }

        /* Notification bell */

        .notification-link {
            position: relative;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--dark-olive);
            border-radius: 50%;
            text-decoration: none;
            color: var(--white);
            background: var(--dark-olive);
            flex-shrink: 0;
            transition: background .2s ease;
        }

        .notification-link:hover {
            color: var(--white);
            background: var(--olive);
        }

        .notification-link svg {
            display: block;
            width: 20px;
            height: 20px;
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #b84f37;
            color: var(--white);
            border: 2px solid var(--paper);
            font-size: 10px;
            font-weight: bold;
            border-radius: 20px;
        }

        /* =========================================
           SHARED PAGE CONTENT
        ========================================= */

        .page-content {
            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            padding: 28px 4% 38px;
        }

        .eyebrow {
            color: var(--olive);
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .page-title {
            font-family: Georgia, serif;
            font-size: clamp(32px, 4vw, 46px);
            font-weight: normal;
            line-height: 1.2;
            margin: 10px 0;
        }

        .page-intro {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
            margin: 0 0 28px;
            max-width: 800px;
        }


.footer-note {
    color: #f5f3e8;
    background: #414c2c;
    font-size: 12px;
    line-height: 1.7;
    margin-top: 36px;
    padding: 20px;
    border-top: 1px solid #55613a;
}

.footer-note a {
    color: #f5f3e8;
    text-decoration: underline;
}


        /* =========================================
           ALERTS
        ========================================= */

        .alert {
            padding: 13px 16px;
            border-radius: 9px;
            margin-bottom: 14px;
            font-size: 14px;
            line-height: 1.6;
        }

        .alert.success {
            background: var(--success);
            color: #276738;
        }

        .alert.error {
            background: var(--danger);
            color: #a33128;
        }

        .alert.validation {
            background: var(--warning);
            color: #805b00;
        }

        /* =========================================
           COMMON BUTTONS
        ========================================= */

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 8px;
            padding: 11px 15px;
            font-family: inherit;
            font-size: 13px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            line-height: 1.4;
            transition: background .2s ease, color .2s ease;
        }

        .btn.primary {
            background: var(--olive);
            color: var(--white);
        }

        .btn.primary:hover {
            background: var(--dark-olive);
        }

        .btn.danger {
            background: var(--danger);
            color: #a33128;
        }

        .btn.outline {
            background: var(--white);
            border: 1px solid var(--border);
            color: var(--olive);
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
        }

        .empty {
            padding: 35px 20px;
            text-align: center;
            color: var(--muted);
            background: var(--white);
            border: 1px dashed #c8c9b7;
            border-radius: 14px;
            line-height: 1.7;
        }

        .pagination {
            margin-top: 24px;
            overflow-x: auto;
        }

        .pagination nav {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* =========================================
           RESPONSIVE DESIGN
        ========================================= */

        @media (max-width: 900px) {
            .admin-layout {
                display: block;
            }

            .sidebar {
                width: 100%;
                min-height: auto;
                padding: 22px 20px;
            }

            .side-note {
                margin-bottom: 18px;
            }

            .sidebar nav {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 4px 8px;
            }

            .nav-label {
                grid-column: 1 / -1;
                margin-top: 12px;
            }

            .sidebar-bottom {
                margin-top: 15px;
                padding-top: 10px;
            }

            .nav-link.back-link {
                margin-top: 4px;
            }

            .page-content {
                padding: 26px 5% 34px;
            }
        }

        @media (max-width: 520px) {
            .sidebar {
                padding: 20px 15px;
            }

            .sidebar nav {
                grid-template-columns: 1fr;
            }

            .topbar {
                min-height: 68px;
                padding: 15px 5%;
                align-items: center;
                gap: 10px;
            }

            .topbar-label {
                font-size: 11px;
            }

            .topbar-actions {
                gap: 9px;
            }

            .admin-label {
                padding: 8px 9px;
                font-size: 11px;
            }

            .notification-link {
                width: 36px;
                height: 36px;
            }

            .page-content {
                padding: 22px 4% 30px;
            }

            .page-title {
                font-size: 32px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
<div class="admin-layout">

    {{-- SHARED ADMIN SIDEBAR --}}
    <aside class="sidebar">

        <a href="{{ route('admin.dashboard') }}" class="brand">
            ARTFY
        </a>

        <div class="side-note">Gallery Administration</div>

        <nav>
            <div class="nav-label">Workspace</div>

            <a
                href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            >
                ⌂ &nbsp; Overview
            </a>

            <a
                href="{{ route('admin.sales.index') }}"
                class="nav-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}"
            >
                ৳ &nbsp; Sales &amp; Commission
            </a>

            <a
                href="{{ route('admin.exhibitions.index') }}"
                class="nav-link {{ request()->routeIs('admin.exhibitions.*') ? 'active' : '' }}"
            >
                ◇ &nbsp; Online Exhibitions
            </a>

            <a
                href="{{ route('admin.memberships.index') }}"
                class="nav-link {{ request()->routeIs('admin.memberships.*') ? 'active' : '' }}"
            >
                ♙ &nbsp; Artist Memberships
            </a>

            <a
                href="{{ route('admin.payments.index') }}"
                class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"
            >
                ◈ &nbsp; Payment Verification
            </a>

            <div class="nav-label">Community Management</div>

            <a
                href="{{ route('admin.artworks.index') }}"
                class="nav-link {{ request()->routeIs('admin.artworks.*') ? 'active' : '' }}"
            >
                ▧ &nbsp; All Artworks
            </a>

            <a
                href="{{ route('admin.artists.index') }}"
                class="nav-link {{ request()->routeIs('admin.artists.*') ? 'active' : '' }}"
            >
                ♙ &nbsp; All Artists
            </a>

            <a
                href="{{ route('admin.art-lovers.index') }}"
                class="nav-link {{ request()->routeIs('admin.art-lovers.*') ? 'active' : '' }}"
            >
                ♡ &nbsp; All Art Lovers
            </a>

            <div class="nav-label">Reports</div>

            <a
                href="{{ route('admin.reports.index') }}"
                class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
            >
                ▤ &nbsp; Reports &amp; Activity
            </a>
        </nav>

        <div class="sidebar-bottom">

            <a href="{{ route('home') }}" class="nav-link back-link">
                ← &nbsp; Back to Artfy
            </a>

            <form
                method="POST"
                action="{{ route('logout') }}"
                class="logout-form"
            >
                @csrf

                <button type="submit" class="nav-link logout-button">
                    ↪ &nbsp; Log out
                </button>
            </form>

        </div>
    </aside>

    {{-- MAIN ADMIN CONTENT --}}
    <div class="main">

        {{-- Transparent header: no white bar or divider --}}
        <header class="topbar">

            <div class="topbar-label">
                Artfy Administration / @yield('page_title', 'Overview')
            </div>

            <div class="topbar-actions">

                @php
                    $adminUser = auth()->user();

                    $unreadNotificationCount = $adminUser
                        ? $adminUser->unreadNotifications()->count()
                        : 0;
                @endphp

                <a
                    href="{{ route('notifications.index') }}"
                    class="notification-link"
                    aria-label="Notifications"
                    title="Notifications"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                        <path d="M10 21h4" />
                    </svg>

                    @if ($unreadNotificationCount > 0)
                        <span class="notification-count">
                            {{ $unreadNotificationCount > 99
                                ? '99+'
                                : $unreadNotificationCount }}
                        </span>
                    @endif
                </a>

                <span class="admin-label">Administrator</span>

            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="page-content">

            <div class="eyebrow">
                @yield('page_eyebrow', 'Artfy Administration')
            </div>

            <h1 class="page-title">
                @yield('page_title', 'Overview')
            </h1>

            @hasSection('page_intro')
                <p class="page-intro">
                    @yield('page_intro')
                </p>
            @endif

            @yield('content')

            <footer class="footer-note">
                Artfy Administration ·
                <a href="{{ route('home') }}">Return to Artfy</a>
            </footer>

        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
