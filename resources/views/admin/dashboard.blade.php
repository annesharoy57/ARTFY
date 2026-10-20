@extends('admin.layout')

@section('title', 'Admin Dashboard | Artfy')

@section('page_eyebrow', 'Artfy / Administration')

@section('page_title', 'Admin Dashboard')

@section('page_intro', 'Manage memberships, payments, artworks and registered users from one place.')

@push('styles')
<style>
    /* Dashboard-specific styles.
       The sidebar and notification header come from admin.layout. */

    .dashboard-section-heading {
        font-family: Georgia, serif;
        font-size: 23px;
        font-weight: normal;
        color: var(--dark-olive);
        margin: 32px 0 16px;
    }

    .dashboard-section-intro {
        color: var(--muted);
        line-height: 1.7;
        font-size: 14px;
        margin: -5px 0 20px;
    }

    /* Statistics cards */

    .dashboard-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .dashboard-stat-card {
        min-width: 0;
        padding: 22px;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .dashboard-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(65, 76, 44, .07);
    }

    .dashboard-stat-label {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .dashboard-stat-value {
        color: var(--dark-olive);
        font-size: 30px;
        font-weight: bold;
        line-height: 1.3;
        margin: 15px 0 8px;
        overflow-wrap: anywhere;
    }

    .dashboard-stat-note {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.6;
    }

    /* Management cards */

    .dashboard-management-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .dashboard-management-card {
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 23px;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .dashboard-management-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(65, 76, 44, .07);
    }

    .dashboard-card-icon {
        width: 48px;
        height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        border-radius: 12px;
        background: var(--cream);
        color: var(--olive);
        font-size: 23px;
    }

    .dashboard-management-card h3 {
        margin: 0 0 10px;
        color: var(--dark-olive);
        font-family: Georgia, serif;
        font-size: 19px;
        font-weight: normal;
        line-height: 1.4;
    }

    .dashboard-management-card p {
        flex: 1;
        margin: 0 0 20px;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.8;
    }

    .dashboard-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        max-width: 100%;
        padding: 11px 15px;
        border: 0;
        border-radius: 8px;
        background: var(--olive);
        color: var(--white);
        font-size: 13px;
        font-weight: bold;
        line-height: 1.5;
        text-align: center;
        text-decoration: none;
        overflow-wrap: anywhere;
        transition: background .2s ease;
    }

    .dashboard-button:hover {
        background: var(--dark-olive);
        color: var(--white);
    }

    .dashboard-button:focus-visible {
        outline: 2px solid var(--olive);
        outline-offset: 3px;
    }

    /* Session messages */

    .dashboard-alert {
        padding: 14px 18px;
        margin-bottom: 15px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 14px;
        line-height: 1.7;
    }

    .dashboard-alert-success {
        background: var(--success);
        border-color: #c9e5cb;
        color: #276738;
    }

    .dashboard-alert-error {
        background: var(--danger);
        border-color: #f0cbc7;
        color: #a33128;
    }

    .dashboard-alert-validation {
        background: var(--warning);
        border-color: #f0dfae;
        color: #805b00;
    }

    .dashboard-alert ul {
        margin: 8px 0 0;
        padding-left: 22px;
    }

    .dashboard-footer-note {
        margin-top: 32px;
        padding-top: 20px;
        border-top: 1px solid var(--border);
        color: var(--muted);
        font-size: 12px;
        line-height: 1.8;
    }

    .dashboard-footer-note a {
        color: var(--olive);
        font-weight: bold;
        text-decoration: none;
    }

    .dashboard-footer-note a:hover {
        text-decoration: underline;
    }

    /* Responsive dashboard */

    @media (max-width: 1150px) {
        .dashboard-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-management-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .dashboard-stats-grid,
        .dashboard-management-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-stat-card,
        .dashboard-management-card {
            padding: 20px;
        }

        .dashboard-section-heading {
            font-size: 21px;
        }
    }
</style>
@endpush

@section('content')

    {{-- SESSION MESSAGES --}}

    @if (session('success'))
        <div class="dashboard-alert dashboard-alert-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="dashboard-alert dashboard-alert-error" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="dashboard-alert dashboard-alert-validation" role="alert">
            <strong>Please check the following:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- PLATFORM OVERVIEW --}}

    <section aria-labelledby="platform-overview-heading">

        <h2 id="platform-overview-heading" class="dashboard-section-heading">
            Platform Overview
        </h2>

        <div class="dashboard-stats-grid">

            <article class="dashboard-stat-card">
                <div class="dashboard-stat-label">
                    Total Artist Applications
                </div>

                <div class="dashboard-stat-value">
                    {{ $totalApplications ?? 0 }}
                </div>

                <div class="dashboard-stat-note">
                    All submitted membership applications
                </div>
            </article>

            <article class="dashboard-stat-card">
                <div class="dashboard-stat-label">
                    Pending Applications
                </div>

                <div class="dashboard-stat-value">
                    {{ $pendingApplications ?? 0 }}
                </div>

                <div class="dashboard-stat-note">
                    Awaiting your decision
                </div>
            </article>

            <article class="dashboard-stat-card">
                <div class="dashboard-stat-label">
                    Payments to Verify
                </div>

                <div class="dashboard-stat-value">
                    {{ $paymentsToVerify ?? 0 }}
                </div>

                <div class="dashboard-stat-note">
                    Membership payments awaiting verification
                </div>
            </article>

            <article class="dashboard-stat-card">
                <div class="dashboard-stat-label">
                    Verified Membership Payments
                </div>

                <div class="dashboard-stat-value">
                    {{ $totalPayments ?? 0 }}
                </div>

                <div class="dashboard-stat-note">
                    Membership payments verified by an administrator
                </div>
            </article>

        </div>
    </section>


    {{-- MANAGEMENT CENTRE --}}

    <section aria-labelledby="management-centre-heading">

        <h2 id="management-centre-heading" class="dashboard-section-heading">
            Management Centre
        </h2>

        <p class="dashboard-section-intro">
            Choose a section to open its dedicated management page.
        </p>

        <div class="dashboard-management-grid">

            <article class="dashboard-management-card">
                <div class="dashboard-card-icon" aria-hidden="true">♙</div>

                <h3>Artist Memberships</h3>

                <p>
                    Review artist applications, biographies, portfolios and
                    membership eligibility.
                </p>

                <a href="{{ route('admin.memberships.index') }}"
                   class="dashboard-button">
                    View Applications →
                </a>
            </article>


            <article class="dashboard-management-card">
                <div class="dashboard-card-icon" aria-hidden="true">৳</div>

                <h3>Payment Verification</h3>

                <p>
                    Check submitted transaction IDs and verify payments for
                    approved memberships.
                </p>

                <a href="{{ route('admin.payments.index') }}"
                   class="dashboard-button">
                    Review Payments →
                </a>
            </article>


            <article class="dashboard-management-card">
                <div class="dashboard-card-icon" aria-hidden="true">৳</div>

                <h3>Sales &amp; Commission</h3>

                <p>
                    View verified artwork sales, total sales value, Artfy's
                    recorded commission, artist earnings and the complete
                    sales ledger.
                </p>

                <a href="{{ route('admin.sales.index') }}"
                   class="dashboard-button">
                    View Sales &amp; Commission →
                </a>
            </article>


            <article class="dashboard-management-card">
                <div class="dashboard-card-icon" aria-hidden="true">▧</div>

                <h3>All Artworks</h3>

                <p>
                    Inspect artwork submissions and approve or reject artworks.
                </p>

                <a href="{{ route('admin.artworks.index') }}"
                   class="dashboard-button">
                    Manage Artworks →
                </a>
            </article>


            <article class="dashboard-management-card">
                <div class="dashboard-card-icon" aria-hidden="true">♙</div>

                <h3>All Artists</h3>

                <p>
                    Browse registered artists and inspect their profiles.
                </p>

                <a href="{{ route('admin.artists.index') }}"
                   class="dashboard-button">
                    Manage Artists →
                </a>
            </article>


            <article class="dashboard-management-card">
                <div class="dashboard-card-icon" aria-hidden="true">♡</div>

                <h3>All Art Lovers</h3>

                <p>
                    View registered art-lover accounts and their details.
                </p>

                <a href="{{ route('admin.art-lovers.index') }}"
                   class="dashboard-button">
                    Manage Art Lovers →
                </a>
            </article>


            <article class="dashboard-management-card">
                <div class="dashboard-card-icon" aria-hidden="true">▤</div>

                <h3>Reports &amp; Activity</h3>

                <p>
                    Review membership totals, payment statuses and recent
                    application records.
                </p>

                <a href="{{ route('admin.reports.index') }}"
                   class="dashboard-button">
                    View Reports →
                </a>
            </article>

        </div>
    </section>


    <footer class="dashboard-footer-note">
        Artfy Administration · Manage your gallery responsibly.

        <a href="{{ route('home') }}">
            Return to Discover
        </a>
    </footer>

@endsection
