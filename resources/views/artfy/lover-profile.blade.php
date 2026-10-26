@extends('artfy.layout')

@section('title', 'My Profile | Artfy')

@section('content')
<div class="lover-profile-page">

    {{-- Success message --}}
    @if (session('success'))
        <div class="lover-alert" role="status">
            <span class="lover-alert-icon">✓</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Profile heading --}}
    <header class="lover-page-heading">
        <span class="lover-eyebrow">ARTFY · YOUR PERSONAL SPACE</span>
        <h1>Your Art Journey</h1>
        <p>A little space for you in the world of art.</p>
    </header>

    <main class="lover-profile-card">

        {{-- Profile picture and introduction --}}
        <section class="lover-profile-intro">

            <div class="lover-avatar-wrap">
                @if (!empty($user->profile_photo))
                    <img
                        src="{{ asset('storage/' . ltrim($user->profile_photo, '/')) }}"
                        alt="{{ $user->name }}'s profile picture"
                        class="lover-avatar"
                        onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                    >
                @endif

                <div
                    class="lover-avatar-placeholder"
                    @if (!empty($user->profile_photo)) hidden @endif
                    aria-label="Profile picture placeholder"
                >
                    {{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}
                </div>
            </div>

            <div class="lover-intro-text">
                <span class="lover-role-label">
                    <span class="lover-role-dot"></span>
                    ART LOVER
                </span>

                <h2>{{ $user->name }}</h2>

                <p>
                    Discover beautiful art, find inspiration,
                    and connect with creativity.
                </p>

                <div class="lover-action-buttons">
                    <a
                        href="{{ route('my-profile.edit') }}"
                        class="lover-btn lover-btn-primary"
                    >
                        <span aria-hidden="true">✎</span>
                        Edit Profile
                    </a>

                    <a
                        href="{{ route('home') }}"
                        class="lover-btn lover-btn-secondary"
                    >
                        Explore Art
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
            </div>

        </section>

        <div class="lover-divider">
            <span></span>
            <span class="lover-divider-symbol">✦</span>
            <span></span>
        </div>

        {{-- Personal information --}}
        <section class="lover-personal-info">
            <div class="lover-section-heading">
                <div>
                    <span class="lover-section-kicker">ABOUT YOU</span>
                    <h3>Personal Information</h3>
                </div>

                <a
                    href="{{ route('my-profile.edit') }}"
                    class="lover-edit-link"
                >
                    Edit details <span aria-hidden="true">↗</span>
                </a>
            </div>

            <div class="lover-info-grid">

                {{-- Name --}}
                <div class="lover-info-item">
                    <div class="lover-info-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="3.5"/>
                            <path d="M5 20c0-4 2.8-6.5 7-6.5s7 2.5 7 6.5"/>
                        </svg>
                    </div>

                    <div class="lover-info-content">
                        <span class="lover-info-label">Full Name</span>
                        <p>{{ $user->name ?: 'Not provided' }}</p>
                    </div>
                </div>

                {{-- Email --}}
                <div class="lover-info-item">
                    <div class="lover-info-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="3.5" y="5.5" width="17" height="13" rx="2"/>
                            <path d="m4.5 7 7.5 6 7.5-6"/>
                        </svg>
                    </div>

                    <div class="lover-info-content">
                        <span class="lover-info-label">Email Address</span>
                        <p>{{ $user->email ?: 'Not provided' }}</p>
                    </div>
                </div>

                {{-- Phone --}}
                <div class="lover-info-item">
                    <div class="lover-info-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M7 3.5h10a1.5 1.5 0 0 1 1.5 1.5v14a1.5 1.5 0 0 1-1.5 1.5H7A1.5 1.5 0 0 1 5.5 19V5A1.5 1.5 0 0 1 7 3.5Z"/>
                            <path d="M10 17.5h4"/>
                        </svg>
                    </div>

                    <div class="lover-info-content">
                        <span class="lover-info-label">Phone Number</span>
                        <p>{{ $user->phone ?: 'Not provided' }}</p>
                    </div>
                </div>

                {{-- Address --}}
                <div class="lover-info-item">
                    <div class="lover-info-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/>
                            <circle cx="12" cy="10" r="2.2"/>
                        </svg>
                    </div>

                    <div class="lover-info-content">
                        <span class="lover-info-label">Address</span>
                        <p>{{ $user->address ?: 'Not provided' }}</p>
                    </div>
                </div>

            </div>
        </section>

        {{-- Art lover inspiration section --}}
        <section class="lover-journey-section">
            <div class="lover-journey-decoration" aria-hidden="true">
                <span class="lover-journey-star">✦</span>
                <span class="lover-journey-circle"></span>
            </div>

            <div class="lover-journey-content">
                <span class="lover-journey-kicker">
                    A WORLD OF CREATIVITY
                </span>

                <h3>Your taste, your art journey.</h3>

                <p>
                    Every artwork has a feeling, a story, and a world
                    of its own. Take your time exploring and discover
                    the pieces that speak to you.
                </p>

                <a href="{{ route('home') }}" class="lover-journey-link">
                    Find your inspiration
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </section>

        <footer class="lover-profile-footer">
            <span aria-hidden="true">✦</span>
            Your account, your preferences, your art journey.
        </footer>

    </main>
</div>

<style>
/* =========================================
   ARTFY ART LOVER PROFILE
   Scoped styles to avoid changing artist pages
========================================= */

.lover-profile-page {
    --lover-cream: #f5f3e8;
    --lover-olive: #55613a;
    --lover-dark: #414c2c;
    --lover-muted: #72795f;
    --lover-border: #e6e5d9;
    --lover-white: #fffef9;

    width: 100%;
    min-height: 80vh;
    padding: 54px 24px 70px;
    color: var(--lover-dark);
    background:
        radial-gradient(
            circle at 8% 8%,
            rgba(114, 121, 95, 0.08),
            transparent 26%
        ),
        var(--lover-cream);
    font-family: inherit;
    box-sizing: border-box;
}

.lover-profile-page *,
.lover-profile-page *::before,
.lover-profile-page *::after {
    box-sizing: border-box;
}

.lover-page-heading {
    max-width: 850px;
    margin: 0 auto 30px;
    text-align: center;
}

.lover-eyebrow,
.lover-section-kicker,
.lover-journey-kicker {
    display: inline-block;
    color: var(--lover-muted);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2px;
    line-height: 1.6;
}

.lover-page-heading h1 {
    margin: 10px 0 8px;
    color: var(--lover-dark);
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(30px, 4vw, 43px);
    font-weight: 500;
    line-height: 1.2;
}

.lover-page-heading > p {
    margin: 0;
    color: var(--lover-muted);
    font-size: 14px;
    line-height: 1.8;
}

/* Success message */

.lover-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 850px;
    margin: 0 auto 24px;
    padding: 14px 18px;
    border: 1px solid #d8dfc9;
    border-radius: 12px;
    background: #fafff3;
    color: var(--lover-dark);
    font-size: 14px;
}

.lover-alert-icon {
    display: inline-grid;
    width: 25px;
    height: 25px;
    flex-shrink: 0;
    place-items: center;
    border-radius: 50%;
    background: #e5ecd9;
    color: var(--lover-olive);
    font-weight: 700;
}

/* Main card */

.lover-profile-card {
    width: 100%;
    max-width: 850px;
    margin: 0 auto;
    padding: clamp(22px, 4vw, 42px);
    overflow: hidden;
    border: 1px solid rgba(85, 97, 58, 0.12);
    border-radius: 24px;
    background: var(--lover-white);
    box-shadow: 0 18px 55px rgba(65, 76, 44, 0.07);
}

/* Intro */

.lover-profile-intro {
    display: flex;
    align-items: center;
    gap: 30px;
}

.lover-avatar-wrap {
    position: relative;
    display: grid;
    width: 150px;
    height: 150px;
    flex: 0 0 150px;
    place-items: center;
    overflow: hidden;
    border: 5px solid #efefe3;
    border-radius: 50%;
    background: #e8eadb;
    box-shadow: 0 0 0 1px rgba(85, 97, 58, 0.12);
}

.lover-avatar {
    display: block;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    object-position: center;
}

.lover-avatar-placeholder {
    display: grid;
    width: 100%;
    height: 100%;
    place-items: center;
    border-radius: 50%;
    background: linear-gradient(145deg, #e7e9d9, #d6dbc5);
    color: var(--lover-olive);
    font-family: Georgia, "Times New Roman", serif;
    font-size: 58px;
    font-weight: 500;
}

.lover-intro-text {
    min-width: 0;
    flex: 1;
}

.lover-role-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: var(--lover-olive);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.7px;
}

.lover-role-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--lover-olive);
}

.lover-intro-text h2 {
    margin: 0 0 8px;
    overflow-wrap: anywhere;
    color: var(--lover-dark);
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(28px, 4vw, 36px);
    font-weight: 500;
    line-height: 1.2;
}

.lover-intro-text > p {
    max-width: 440px;
    margin: 0;
    color: var(--lover-muted);
    font-size: 14px;
    line-height: 1.8;
}

/* Buttons */

.lover-action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 22px;
}

.lover-btn {
    display: inline-flex;
    min-height: 44px;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 11px 19px;
    border: 1px solid var(--lover-olive);
    border-radius: 10px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.4;
    text-align: center;
    text-decoration: none;
    transition:
        background 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease,
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.lover-btn:hover {
    transform: translateY(-2px);
}

.lover-btn-primary {
    background: var(--lover-olive);
    color: #fff;
    box-shadow: 0 5px 12px rgba(85, 97, 58, 0.12);
}

.lover-btn-primary:hover {
    border-color: var(--lover-dark);
    background: var(--lover-dark);
    color: #fff;
    box-shadow: 0 7px 18px rgba(65, 76, 44, 0.16);
}

.lover-btn-secondary {
    background: transparent;
    color: var(--lover-olive);
}

.lover-btn-secondary:hover {
    background: var(--lover-cream);
    color: var(--lover-dark);
}

.lover-btn:focus-visible,
.lover-edit-link:focus-visible,
.lover-journey-link:focus-visible {
    outline: 3px solid rgba(85, 97, 58, 0.35);
    outline-offset: 4px;
}

/* Decorative divider */

.lover-divider {
    display: flex;
    align-items: center;
    gap: 14px;
    margin: 34px 0;
}

.lover-divider > span:not(.lover-divider-symbol) {
    height: 1px;
    flex: 1;
    background: var(--lover-border);
}

.lover-divider-symbol {
    color: var(--lover-olive);
    font-size: 17px;
}

/* Personal information */

.lover-personal-info {
    margin-bottom: 34px;
}

.lover-section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 22px;
}

.lover-section-heading h3 {
    margin: 5px 0 0;
    color: var(--lover-dark);
    font-family: Georgia, "Times New Roman", serif;
    font-size: 24px;
    font-weight: 500;
    line-height: 1.35;
}

.lover-edit-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    flex-shrink: 0;
    color: var(--lover-olive);
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.lover-edit-link:hover {
    color: var(--lover-dark);
    text-decoration: underline;
    text-underline-offset: 4px;
}

.lover-info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.lover-info-item {
    display: flex;
    min-width: 0;
    align-items: flex-start;
    gap: 13px;
    padding: 17px;
    border: 1px solid var(--lover-border);
    border-radius: 13px;
    background: #fffefb;
    transition:
        border-color 0.2s ease,
        background 0.2s ease,
        transform 0.2s ease;
}

.lover-info-item:hover {
    transform: translateY(-2px);
    border-color: #cbd2b9;
    background: #fcfcf5;
}

.lover-info-icon {
    display: grid;
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    place-items: center;
    border-radius: 11px;
    background: #eff0e5;
    color: var(--lover-olive);
}

.lover-info-icon svg {
    width: 21px;
    height: 21px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.6;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.lover-info-content {
    min-width: 0;
    padding-top: 2px;
}

.lover-info-label {
    display: block;
    margin-bottom: 5px;
    color: var(--lover-muted);
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.4px;
}

.lover-info-content p {
    margin: 0;
    overflow-wrap: anywhere;
    color: var(--lover-dark);
    font-size: 13px;
    font-weight: 600;
    line-height: 1.7;
}

/* Art journey feature */

.lover-journey-section {
    position: relative;
    display: flex;
    min-height: 190px;
    align-items: center;
    gap: 20px;
    overflow: hidden;
    padding: 28px;
    border: 1px solid #e2e5d5;
    border-radius: 17px;
    background:
        radial-gradient(
            circle at 100% 0%,
            rgba(114, 121, 95, 0.14),
            transparent 38%
        ),
        linear-gradient(120deg, #f1f2e8, #f8f7ef);
}

.lover-journey-decoration {
    position: relative;
    display: grid;
    width: 66px;
    height: 66px;
    flex: 0 0 66px;
    place-items: center;
}

.lover-journey-star {
    position: relative;
    z-index: 1;
    color: var(--lover-olive);
    font-size: 43px;
}

.lover-journey-circle {
    position: absolute;
    width: 60px;
    height: 60px;
    border: 1px solid rgba(85, 97, 58, 0.32);
    border-radius: 50%;
}

.lover-journey-content {
    position: relative;
    z-index: 1;
    min-width: 0;
    flex: 1;
}

.lover-journey-content h3 {
    margin: 7px 0 9px;
    color: var(--lover-dark);
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(21px, 3vw, 27px);
    font-weight: 500;
    line-height: 1.35;
}

.lover-journey-content p {
    max-width: 540px;
    margin: 0;
    color: var(--lover-muted);
    font-size: 13px;
    line-height: 1.9;
}

.lover-journey-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 15px;
    color: var(--lover-olive);
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
}

.lover-journey-link:hover {
    gap: 12px;
    color: var(--lover-dark);
}

.lover-journey-link span {
    transition: transform 0.2s ease;
}

.lover-journey-link:hover span {
    transform: translateX(2px);
}

/* Footer */

.lover-profile-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 26px;
    color: var(--lover-muted);
    font-size: 11px;
    line-height: 1.8;
    text-align: center;
}

.lover-profile-footer span {
    color: var(--lover-olive);
}

/* Tablet */

@media (max-width: 650px) {
    .lover-profile-page {
        padding: 36px 16px 48px;
    }

    .lover-profile-card {
        padding: 24px 20px;
        border-radius: 19px;
    }

    .lover-profile-intro {
        align-items: center;
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }

    .lover-avatar-wrap {
        width: 125px;
        height: 125px;
        flex-basis: 125px;
    }

    .lover-intro-text > p {
        margin: 0 auto;
    }

    .lover-action-buttons {
        justify-content: center;
    }

    .lover-info-grid {
        grid-template-columns: 1fr;
    }

    .lover-journey-section {
        align-items: flex-start;
        gap: 14px;
        padding: 23px 18px;
    }

    .lover-journey-decoration {
        width: 44px;
        height: 44px;
        flex-basis: 44px;
    }

    .lover-journey-star {
        font-size: 31px;
    }

    .lover-journey-circle {
        width: 43px;
        height: 43px;
    }
}

/* Small phones */

@media (max-width: 390px) {
    .lover-profile-page {
        padding-right: 12px;
        padding-left: 12px;
    }

    .lover-profile-card {
        padding: 22px 15px;
    }

    .lover-action-buttons {
        align-items: stretch;
        flex-direction: column;
    }

    .lover-btn {
        width: 100%;
    }

    .lover-section-heading h3 {
        font-size: 21px;
    }

    .lover-edit-link {
        font-size: 11px;
    }

    .lover-info-item {
        padding: 14px 12px;
    }

    .lover-journey-decoration {
        display: none;
    }
}

/* Reduced motion accessibility */

@media (prefers-reduced-motion: reduce) {
    .lover-profile-page *,
    .lover-profile-page *::before,
    .lover-profile-page *::after {
        scroll-behavior: auto !important;
        transition: none !important;
    }

    .lover-info-item:hover,
    .lover-btn:hover {
        transform: none;
    }
}
</style>
@endsection
