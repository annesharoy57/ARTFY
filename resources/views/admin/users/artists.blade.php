@extends('admin.layout')

@section('title', 'All Artists | Artfy Admin')

@section('page_eyebrow', 'Artfy / Community Management')

@section('page_title', 'All Artists')

@section('page_intro', 'Meet the artists who have joined Artfy. Open an artist’s profile to inspect their profile information and uploaded artwork.')

@push('styles')
<style>
    .artists-page {
        width: 100%;
    }

    .artists-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 20px 24px;
        margin-bottom: 28px;
        background: var(--cream, #efede4);
        border: 1px solid var(--border, #e5e1d7);
        border-radius: 14px;
    }

    .artists-summary-label {
        color: var(--muted, #77786e);
        font-size: 13px;
        line-height: 1.6;
    }

    .artists-summary-count {
        display: block;
        margin-bottom: 3px;
        color: var(--dark-olive, #414c2c);
        font-family: Georgia, serif;
        font-size: 30px;
        line-height: 1.2;
    }

    .artists-summary-note {
        color: var(--muted, #77786e);
        font-size: 12px;
    }

    .artists-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(225px, 1fr));
        gap: 22px;
    }

    .artist-directory-card {
        display: block;
        min-width: 0;
        overflow: hidden;
        color: inherit;
        text-decoration: none;
        background: var(--white, #fff);
        border: 1px solid var(--border, #e5e1d7);
        border-radius: 15px;
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            border-color .2s ease;
    }

    .artist-directory-card:hover {
        transform: translateY(-4px);
        border-color: #c9cdbb;
        box-shadow: 0 12px 28px rgba(65, 76, 44, .09);
    }

    .artist-directory-card:focus-visible {
        outline: 3px solid var(--olive, #55613a);
        outline-offset: 4px;
    }

    .artist-photo-wrap {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 220px;
        overflow: hidden;
        background: var(--cream, #efede4);
    }

    .artist-photo {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .artist-avatar {
        display: grid;
        place-items: center;
        width: 104px;
        height: 104px;
        flex-shrink: 0;
        color: white;
        background: var(--olive, #55613a);
        border: 5px solid rgba(255, 255, 255, .55);
        border-radius: 50%;
        font-family: Georgia, serif;
        font-size: 40px;
    }

    .artist-card-body {
        padding: 19px;
    }

    .artist-card-name {
        margin: 0 0 10px;
        color: var(--text, #28291f);
        font-family: Georgia, serif;
        font-size: 22px;
        font-weight: normal;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .artist-detail {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin: 7px 0;
        color: var(--muted, #77786e);
        font-size: 13px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .artist-detail-icon {
        width: 17px;
        flex-shrink: 0;
        color: var(--olive, #55613a);
        text-align: center;
    }

    .artist-status {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        margin-top: 12px;
        padding: 7px 10px;
        color: var(--dark-olive, #414c2c);
        background: var(--cream, #efede4);
        border: 1px solid transparent;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .artist-status.is-approved {
        color: #386347;
        background: #edf5ed;
        border-color: #d5e7d6;
    }

    .artist-status.is-pending {
        color: #87631c;
        background: #fbf3df;
        border-color: #f0e2b9;
    }

    .artist-status.is-rejected {
        color: #963f35;
        background: #faece9;
        border-color: #f0d5cf;
    }

    .artists-empty {
        padding: 48px 24px;
        text-align: center;
        background: var(--white, #fff);
        border: 1px dashed var(--border, #e5e1d7);
        border-radius: 15px;
    }

    .artists-empty-icon {
        display: grid;
        place-items: center;
        width: 58px;
        height: 58px;
        margin: 0 auto 16px;
        color: var(--olive, #55613a);
        background: var(--cream, #efede4);
        border-radius: 50%;
        font-size: 25px;
    }

    .artists-empty h2 {
        margin: 0 0 8px;
        color: var(--text, #28291f);
        font-family: Georgia, serif;
        font-size: 24px;
        font-weight: normal;
    }

    .artists-empty p {
        max-width: 420px;
        margin: 0 auto;
        color: var(--muted, #77786e);
        font-size: 14px;
        line-height: 1.7;
    }

    .artists-pagination {
        margin-top: 30px;
        overflow-x: auto;
    }

    .artists-pagination nav {
        display: flex;
        justify-content: center;
    }

    .artists-footer-note {
        margin-top: 34px;
        padding-top: 18px;
        color: var(--muted, #77786e);
        border-top: 1px solid var(--border, #e5e1d7);
        font-size: 12px;
        line-height: 1.7;
    }

    @media (max-width: 640px) {
        .artists-summary {
            padding: 17px;
        }

        .artists-grid {
            grid-template-columns: repeat(
                auto-fill,
                minmax(min(100%, 220px), 1fr)
            );
            gap: 16px;
        }

        .artist-photo-wrap {
            height: 190px;
        }

        .artist-card-body {
            padding: 16px;
        }

        .artist-card-name {
            font-size: 20px;
        }
    }

    @media (max-width: 420px) {
        .artists-grid {
            grid-template-columns: 1fr;
        }

        .artist-photo-wrap {
            height: 230px;
        }
    }
</style>
@endpush

@section('content')
<div class="artists-page">

    {{-- Total artist accounts --}}
    <div class="artists-summary">
        <div>
            <span class="artists-summary-count">
                {{ $artists->total() }}
            </span>

            <div class="artists-summary-label">
                {{ $artists->total() === 1 ? 'Artist account' : 'Artist accounts' }}
                registered on Artfy
            </div>
        </div>

        <div class="artists-summary-note">
            Artist Community Directory
        </div>
    </div>

    {{-- Artist directory --}}
    @if ($artists->isEmpty())
        <div class="artists-empty">
            <div class="artists-empty-icon" aria-hidden="true">♙</div>

            <h2>No artists yet</h2>

            <p>
                No artist accounts were found. Artist accounts will appear
                here when users register as artists.
            </p>
        </div>
    @else
        <div class="artists-grid">
            @foreach ($artists as $artist)
                @php
                    $profile = $artist->artistProfile;
                    $application = $artist->artistApplication;

                    $artistName = $profile?->artist_name ?: $artist->name;
                    $photo = $profile?->profile_photo;

                    $photoUrl = $photo
                        ? asset('storage/' . ltrim($photo, '/'))
                        : null;

                    $membershipApproved =
                        $application?->status === 'approved'
                        && $application?->payment_status === 'verified';

                    $applicationStatus = $application?->status ?? 'not submitted';
                    $paymentStatus = $application?->payment_status ?? 'pending';

                    $initial = mb_strtoupper(
                        mb_substr($artistName, 0, 1)
                    );

                    if ($membershipApproved) {
                        $statusClass = 'is-approved';
                        $statusText = 'Approved artist';
                    } elseif ($applicationStatus === 'rejected') {
                        $statusClass = 'is-rejected';
                        $statusText = 'Membership: Rejected';
                    } elseif ($applicationStatus === 'pending') {
                        $statusClass = 'is-pending';
                        $statusText = 'Membership: Pending';
                    } elseif ($applicationStatus === 'approved') {
                        $statusClass = 'is-pending';
                        $statusText = 'Payment: ' . ucfirst($paymentStatus);
                    } else {
                        $statusClass = '';
                        $statusText = 'Membership: Not submitted';
                    }
                @endphp

                <a
                    class="artist-directory-card"
                    href="{{ route('admin.artists.show', ['user' => $artist->id]) }}"
                    aria-label="View {{ $artistName }}'s artist profile"
                >
                    <div class="artist-photo-wrap">
                        @if ($photoUrl)
                            <img
                                class="artist-photo"
                                src="{{ $photoUrl }}"
                                alt="{{ $artistName }} profile photo"
                                loading="lazy"
                                onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='grid';"
                            >

                            <div
                                class="artist-avatar"
                                style="display: none;"
                                aria-hidden="true"
                            >
                                {{ $initial }}
                            </div>
                        @else
                            <div
                                class="artist-avatar"
                                aria-label="{{ $artistName }} initials"
                            >
                                {{ $initial }}
                            </div>
                        @endif
                    </div>

                    <div class="artist-card-body">
                        <h2 class="artist-card-name">
                            {{ $artistName }}
                        </h2>

                        <p class="artist-detail">
                            <span class="artist-detail-icon" aria-hidden="true">✦</span>
                            <span>
                                {{ $profile?->art_category
                                    ?: $application?->art_category
                                    ?: 'Independent artist' }}
                            </span>
                        </p>

                        <p class="artist-detail">
                            <span class="artist-detail-icon" aria-hidden="true">⌖</span>
                            <span>
                                {{ $profile?->location ?: 'Location not added' }}
                            </span>
                        </p>

                        <span class="artist-status {{ $statusClass }}">
                            {{ $statusText }}
                        </span>

                        @if ($applicationStatus === 'approved' && !$membershipApproved)
                            <p class="artist-detail">
                                <span class="artist-detail-icon" aria-hidden="true">৳</span>
                                <span>
                                    Membership approved; payment
                                    {{ strtolower($paymentStatus) }}.
                                </span>
                            </p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($artists->hasPages())
            <div class="artists-pagination">
                {{ $artists->links() }}
            </div>
        @endif
    @endif

    <footer class="artists-footer-note">
        ARTFY Administration · Artist Directory
    </footer>
</div>
@endsection
