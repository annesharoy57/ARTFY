<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Artfy</title>

    <style>
        :root {
            --cream: #f5f3e8;
            --olive: #55613a;
            --dark-olive: #414c2c;
            --muted: #72795f;
            --white: #ffffff;
            --border: #e5e2d5;
            --success: #e5f3e5;
            --success-text: #276738;
            --warning: #fff1cf;
            --warning-text: #805b00;
            --danger: #fbe8e6;
            --danger-text: #a33128;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 24px;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: var(--cream);
            color: var(--dark-olive);
        }

        a {
            -webkit-tap-highlight-color: transparent;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            width: 255px;
            flex-shrink: 0;
            padding: 30px 18px;
            background: var(--dark-olive);
            color: white;
        }

        .brand {
            font-size: 29px;
            font-weight: bold;
            margin: 0 0 8px;
        }

        .brand span {
            color: #d9dfb9;
        }

        .sidebar-subtitle {
            color: #d5d9c8;
            font-size: 12px;
            margin-bottom: 36px;
        }

        .nav-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #c6ccb3;
            margin: 24px 10px 12px;
        }

        .nav-link {
            display: block;
            color: #f5f3e8;
            text-decoration: none;
            padding: 12px 10px;
            border-radius: 8px;
            margin-bottom: 5px;
            font-size: 14px;
            transition: background 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--olive);
        }

        .back-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            border: 1px solid #899174;
            border-radius: 8px;
            padding: 10px 12px;
            margin-top: 25px;
            font-size: 13px;
        }

        .logout-form {
            margin-top: 12px;
        }

        .logout-button {
            width: 100%;
            border: 1px solid #899174;
            border-radius: 8px;
            padding: 11px;
            background: transparent;
            color: white;
            cursor: pointer;
            text-align: left;
            font-size: 13px;
        }

        .logout-button:hover,
        .back-link:hover {
            background: var(--olive);
        }

        /* MAIN CONTENT */

        .main {
            flex: 1;
            min-width: 0;
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .eyebrow {
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 11px;
            font-weight: bold;
        }

        h1 {
            font-size: clamp(27px, 4vw, 38px);
            margin: 10px 0;
        }

        .subtitle {
            color: var(--muted);
            line-height: 1.7;
            font-size: 14px;
        }

        .admin-badge {
            background: #e5e8d7;
            color: var(--dark-olive);
            border-radius: 30px;
            padding: 11px 16px;
            font-size: 13px;
        }

        .section-heading {
            font-size: 21px;
            margin: 36px 0 16px;
            scroll-margin-top: 24px;
        }

        .section-intro {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
            margin: -5px 0 18px;
        }

        /* STATISTICS */

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .stat-card,
        .management-card,
        .activity-panel,
        .application-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px;
        }

        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(65, 76, 44, 0.07);
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .stat-value {
            font-size: 29px;
            font-weight: bold;
            margin: 15px 0 8px;
        }

        .stat-note {
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }

        /* MANAGEMENT CARDS */

        .management-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .management-card {
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .management-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(65, 76, 44, 0.08);
        }

        .card-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--cream);
            color: var(--olive);
            font-size: 25px;
            margin-bottom: 16px;
        }

        .management-card h3 {
            margin: 0 0 10px;
            font-size: 17px;
        }

        .management-card p {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
            flex: 1;
            margin: 0 0 18px;
        }

        .card-action {
            margin-top: auto;
        }

        /* STATUS */

        .status {
            display: inline-block;
            padding: 7px 10px;
            border-radius: 20px;
            background: #f0eee4;
            color: var(--muted);
            font-size: 11px;
            font-weight: bold;
        }

        .status-pending {
            background: var(--warning);
            color: var(--warning-text);
        }

        .status-approved,
        .status-verified {
            background: var(--success);
            color: var(--success-text);
        }

        .status-rejected,
        .status-unpaid {
            background: var(--danger);
            color: var(--danger-text);
        }

        /* ALERTS */

        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 15px;
            line-height: 1.6;
            font-size: 14px;
        }

        .alert-success {
            background: var(--success);
            color: var(--success-text);
            border: 1px solid #c9e5cb;
        }

        .alert-error {
            background: var(--danger);
            color: var(--danger-text);
            border: 1px solid #f0cbc7;
        }

        .alert-validation {
            background: var(--warning);
            color: var(--warning-text);
            border: 1px solid #f0dfae;
        }

        /* APPLICATIONS */

        .application-list {
            display: grid;
            gap: 18px;
        }

        .application-card {
            scroll-margin-top: 20px;
        }

        .application-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .application-header h3 {
            margin: 0 0 7px;
            font-size: 19px;
            overflow-wrap: anywhere;
        }

        .muted {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 18px;
        }

        .detail-box {
            background: #faf9f4;
            border: 1px solid var(--border);
            padding: 14px;
            border-radius: 10px;
            min-width: 0;
        }

        .detail-label {
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .detail-value {
            font-size: 14px;
            line-height: 1.7;
            overflow-wrap: anywhere;
            white-space: pre-line;
        }

        .portfolio-link {
            color: var(--olive);
            font-weight: bold;
        }

        .portfolio-link:hover {
            color: var(--dark-olive);
        }

        .payment-box {
            background: #f7f6ee;
            border: 1px solid var(--border);
            padding: 16px;
            border-radius: 10px;
            margin-top: 16px;
            scroll-margin-top: 24px;
        }

        .payment-box h4 {
            margin: 0 0 16px;
        }

        .action-area {
            border-top: 1px solid var(--border);
            margin-top: 18px;
            padding-top: 18px;
        }

        .action-area h4 {
            margin: 0 0 12px;
        }

        .action-form {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            color: var(--muted);
            margin-bottom: 7px;
        }

        .admin-notes {
            width: 100%;
            min-height: 72px;
            resize: vertical;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 11px;
            font: inherit;
            font-size: 13px;
            margin-bottom: 10px;
            background: #fff;
            color: var(--dark-olive);
        }

        .admin-notes:focus {
            outline: 2px solid #a6b27e;
            outline-offset: 1px;
        }

        /* BUTTONS */

        .button-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 0;
            border-radius: 8px;
            padding: 11px 15px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            line-height: 1.4;
            transition: background 0.2s, transform 0.2s;
        }

        .button:hover {
            transform: translateY(-1px);
        }

        .button-primary {
            background: var(--olive);
            color: white;
        }

        .button-primary:hover {
            background: var(--dark-olive);
        }

        .button-danger {
            background: var(--danger);
            color: var(--danger-text);
        }

        .button-danger:hover {
            background: #f4d5d1;
        }

        .button-outline {
            background: white;
            color: var(--olive);
            border: 1px solid var(--border);
        }

        .button-outline:hover {
            background: var(--cream);
        }

        .button:focus-visible,
        .nav-link:focus-visible,
        .back-link:focus-visible,
        .logout-button:focus-visible {
            outline: 2px solid #a6b27e;
            outline-offset: 3px;
        }

        /* EMPTY STATES AND ACTIVITY */

        .empty-state {
            padding: 35px 20px;
            text-align: center;
            background: white;
            border: 1px dashed #c8c9b7;
            border-radius: 14px;
        }

        .empty-state h3 {
            margin-top: 0;
        }

        .empty-state p {
            color: var(--muted);
            line-height: 1.7;
            font-size: 14px;
        }

        .activity-panel p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .activity-item {
            padding: 14px 0;
            border-bottom: 1px solid var(--border);
        }

        .activity-item:first-child {
            padding-top: 0;
        }

        .activity-item:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .activity-item strong {
            font-size: 14px;
        }

        .activity-heading {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .footer-note {
            margin-top: 30px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 12px;
            line-height: 1.7;
        }

        /* RESPONSIVE */

        @media (max-width: 1150px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .management-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .main {
                padding: 24px;
            }
        }

        @media (max-width: 750px) {
            .admin-layout {
                display: block;
            }

            .sidebar {
                width: 100%;
                padding: 22px 16px;
            }

            .sidebar-subtitle {
                margin-bottom: 20px;
            }

            .nav-label {
                margin-top: 18px;
            }

            .main {
                padding: 22px 14px;
            }

            .stats-grid,
            .management-grid,
            .details-grid {
                grid-template-columns: 1fr;
            }

            .stat-card,
            .management-card,
            .activity-panel,
            .application-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>
<div class="admin-layout">

    {{-- SIDEBAR --}}

    <aside class="sidebar">
        <div class="brand">Art<span>fy.</span></div>
        <div class="sidebar-subtitle">Gallery administration</div>

        <div class="nav-label">Workspace</div>

        <a href="#overview" class="nav-link active">
            ⌂ &nbsp; Overview
        </a>

        <a href="#memberships" class="nav-link">
            ♙ &nbsp; Artist Memberships
        </a>

        <a href="#payments" class="nav-link">
            ৳ &nbsp; Payments
        </a>

        <a href="{{ route('admin.artworks.index') }}" class="nav-link">
            ▧ &nbsp; All Artworks
        </a>

        <div class="nav-label">People</div>

        <a href="{{ route('admin.artists.index') }}" class="nav-link">
            ♙ &nbsp; All Artists
        </a>

        <a href="{{ route('admin.art-lovers.index') }}" class="nav-link">
            ♧ &nbsp; All Art Lovers
        </a>

        <div class="nav-label">Reports</div>

        <a href="#recent-activity" class="nav-link">
            ▥ &nbsp; Recent Activity
        </a>

        <a href="{{ route('home') }}" class="back-link">
            ← Back to Artfy
        </a>

        <form method="POST"
              action="{{ route('logout') }}"
              class="logout-form">
            @csrf

            <button type="submit" class="logout-button">
                ↪ &nbsp; Log out
            </button>
        </form>
    </aside>


    {{-- MAIN DASHBOARD --}}

    <main class="main">

        <header class="topbar" id="overview">
            <div>
                <div class="eyebrow">Artfy / Administration</div>

                <h1>Admin Dashboard</h1>

                <div class="subtitle">
                    Manage your gallery, artist memberships, payments,
                    artworks and registered users from one place.
                </div>
            </div>

            <div class="admin-badge">
                Administrator: {{ auth()->user()->name }}
            </div>
        </header>


        {{-- SUCCESS MESSAGE --}}

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif


        {{-- ERROR MESSAGE --}}

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif


        {{-- VALIDATION ERRORS --}}

        @if ($errors->any())
            <div class="alert alert-validation">
                <strong>Please check the following:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- PLATFORM STATISTICS --}}

        <h2 class="section-heading">Platform Overview</h2>

        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-label">Total Artist Applications</div>

                <div class="stat-value">
                    {{ $totalApplications ?? 0 }}
                </div>

                <div class="stat-note">
                    All submitted membership applications
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Pending Applications</div>

                <div class="stat-value">
                    {{ $pendingApplications ?? 0 }}
                </div>

                <div class="stat-note">
                    Applications awaiting your decision
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Payments to Verify</div>

                <div class="stat-value">
                    {{ $paymentsToVerify ?? 0 }}
                </div>

                <div class="stat-note">
                    Submitted payments awaiting verification
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Verified Payments</div>

                <div class="stat-value">
                    {{ $totalPayments ?? 0 }}
                </div>

                <div class="stat-note">
                    Payments verified by the administrator
                </div>
            </div>

        </div>


        {{-- QUICK ACCESS MANAGEMENT CENTRE --}}

        <h2 class="section-heading">Management Centre</h2>

        <p class="section-intro">
            Choose a section below to open its dedicated management page.
        </p>

        <div class="management-grid">

            {{-- MEMBERSHIPS --}}

            <section class="management-card">
                <div class="card-icon">♙</div>

                <h3>Artist Memberships</h3>

                <p>
                    Review artist applications, biographies, portfolios
                    and membership eligibility.
                </p>

                <a href="#memberships"
                   class="button button-primary card-action">
                    View Applications →
                </a>
            </section>


            {{-- PAYMENTS --}}

            <section class="management-card">
                <div class="card-icon">৳</div>

                <h3>Payment Verification</h3>

                <p>
                    Check submitted transaction IDs and verify payments
                    for approved artist memberships.
                </p>

                <a href="#payments"
                   class="button button-primary card-action">
                    Review Payments →
                </a>
            </section>


            {{-- ALL ARTWORKS --}}

            <section class="management-card">
                <div class="card-icon">▧</div>

                <h3>All Artworks</h3>

                <p>
                    Open artwork review to inspect submissions and
                    approve or reject artworks.
                </p>

                <a href="{{ route('admin.artworks.index') }}"
                   class="button button-primary card-action">
                    Manage Artworks →
                </a>
            </section>


            {{-- ALL ARTISTS --}}

            <section class="management-card">
                <div class="card-icon">♙</div>

                <h3>All Artists</h3>

                <p>
                    Browse registered artists, view their profile
                    information and inspect their artwork.
                </p>

                <a href="{{ route('admin.artists.index') }}"
                   class="button button-primary card-action">
                    Manage Artists →
                </a>
            </section>


            {{-- ALL ART LOVERS --}}

            <section class="management-card">
                <div class="card-icon">♧</div>

                <h3>All Art Lovers</h3>

                <p>
                    View registered art-lover accounts and open
                    individual account details.
                </p>

                <a href="{{ route('admin.art-lovers.index') }}"
                   class="button button-primary card-action">
                    Manage Art Lovers →
                </a>
            </section>


            {{-- ACTIVITY --}}

            <section class="management-card">
                <div class="card-icon">▥</div>

                <h3>Reports & Activity</h3>

                <p>
                    Review recent membership submissions, application
                    decisions and payment statuses.
                </p>

                <a href="#recent-activity"
                   class="button button-primary card-action">
                    View Activity →
                </a>
            </section>

        </div>


        {{-- MEMBERSHIP APPLICATIONS --}}

        <h2 class="section-heading" id="memberships">
            Artist Membership Applications
        </h2>

        <p class="section-intro">
            Review artist details, approve or reject applications,
            and inspect the submitted payment information.
        </p>


        @if (isset($applications) && $applications->isNotEmpty())

            <div class="application-list">

                @foreach ($applications as $application)

                    <section class="application-card"
                             id="application-{{ $application->id }}">

                        <div class="application-header">

                            <div>
                                <h3>{{ $application->artist_name }}</h3>

                                <div class="muted">
                                    Application #{{ $application->id }}
                                    · Submitted
                                    {{ $application->created_at
                                        ? $application->created_at->format('d M Y, h:i A')
                                        : 'Date unavailable' }}
                                </div>

                                <div class="muted">
                                    Account:
                                    {{ $application->user->name ?? 'Unknown user' }}

                                    @if ($application->user?->email)
                                        · {{ $application->user->email }}
                                    @endif
                                </div>
                            </div>

                            <div>
                                @php
                                    $applicationStatus =
                                        $application->status ?? 'pending';

                                    $applicationStatusClass = in_array(
                                        $applicationStatus,
                                        ['pending', 'approved', 'rejected']
                                    ) ? $applicationStatus : 'pending';
                                @endphp

                                <span class="status status-{{ $applicationStatusClass }}">
                                    {{ ucfirst($applicationStatus) }}
                                </span>
                            </div>

                        </div>


                        {{-- ARTIST DETAILS --}}

                        <div class="details-grid">

                            <div class="detail-box">
                                <div class="detail-label">Biography</div>

                                <div class="detail-value">
                                    {{ $application->biography ?: 'Not provided' }}
                                </div>
                            </div>


                            <div class="detail-box">
                                <div class="detail-label">Art Categories</div>

                                @php
                                    $categories = $application->art_categories ?? [];

                                    if (is_string($categories)) {
                                        $decodedCategories = json_decode(
                                            $categories,
                                            true
                                        );

                                        $categories = is_array($decodedCategories)
                                            ? $decodedCategories
                                            : [];
                                    }

                                    if (
                                        empty($categories)
                                        && $application->art_category
                                    ) {
                                        $categories = [
                                            $application->art_category
                                        ];
                                    }

                                    if (!is_array($categories)) {
                                        $categories = [];
                                    }
                                @endphp

                                <div class="detail-value">
                                    {{ !empty($categories)
                                        ? implode(', ', $categories)
                                        : 'Not specified' }}
                                </div>
                            </div>


                            <div class="detail-box">
                                <div class="detail-label">Portfolio</div>

                                <div class="detail-value">
                                    @if ($application->portfolio_url)
                                        <a class="portfolio-link"
                                           href="{{ $application->portfolio_url }}"
                                           target="_blank"
                                           rel="noopener noreferrer">
                                            Open artist portfolio ↗
                                        </a>
                                    @else
                                        Not provided
                                    @endif
                                </div>
                            </div>


                            <div class="detail-box">
                                <div class="detail-label">Annual Membership Fee</div>

                                <div class="detail-value">
                                    ৳{{ number_format(
                                        (float) ($application->membership_fee ?? 1000)
                                    ) }}
                                    / year
                                </div>
                            </div>

                        </div>


                        {{-- PAYMENT INFORMATION --}}

                        <div class="payment-box">

                            <h4 id="payments">Payment Information</h4>

                            <div class="details-grid">

                                <div>
                                    <div class="detail-label">Payment Status</div>

                                    @php
                                        $paymentStatus =
                                            $application->payment_status ?? 'unpaid';

                                        $paymentStatusClass = in_array(
                                            $paymentStatus,
                                            ['pending', 'verified', 'unpaid']
                                        ) ? $paymentStatus : 'unpaid';
                                    @endphp

                                    <span class="status status-{{ $paymentStatusClass }}">
                                        {{ ucfirst($paymentStatus) }}
                                    </span>
                                </div>


                                <div>
                                    <div class="detail-label">Payment Method</div>

                                    <div class="detail-value">
                                        {{ $application->payment_method
                                            ? ucfirst($application->payment_method)
                                            : 'Manual' }}
                                    </div>
                                </div>


                                <div>
                                    <div class="detail-label">Transaction ID</div>

                                    <div class="detail-value">
                                        @if ($application->payment_reference)
                                            <strong>
                                                {{ $application->payment_reference }}
                                            </strong>
                                        @else
                                            <span class="muted">
                                                No transaction ID submitted yet.
                                            </span>
                                        @endif
                                    </div>
                                </div>


                                <div>
                                    <div class="detail-label">Payment Submitted</div>

                                    <div class="detail-value">
                                        {{ $application->payment_submitted_at
                                            ? $application->payment_submitted_at->format('d M Y, h:i A')
                                            : 'Not submitted' }}
                                    </div>
                                </div>

                            </div>


                            @if ($application->payment_verified_at)
                                <div class="muted">
                                    Verified on
                                    {{ $application->payment_verified_at->format('d M Y, h:i A') }}
                                </div>
                            @endif


                            {{-- VERIFY PAYMENT --}}

                            @if (
                                $application->status === 'approved'
                                && $application->payment_status === 'pending'
                                && filled($application->payment_reference)
                            )

                                <form method="POST"
                                      action="{{ route(
                                          'admin.memberships.verify-payment',
                                          $application
                                      ) }}"
                                      onsubmit="return confirm('Have you checked the transaction? Verify this payment now?');"
                                      style="margin-top: 15px;">

                                    @csrf

                                    <button type="submit"
                                            class="button button-primary">
                                        Verify Payment
                                    </button>

                                </form>

                            @elseif ($application->payment_status === 'verified')

                                <p class="muted">
                                    Payment verified successfully.
                                </p>

                            @elseif (
                                $application->status !== 'approved'
                                && $application->payment_reference
                            )

                                <p class="muted">
                                    Membership must be approved before
                                    payment verification is available.
                                </p>

                            @else

                                <p class="muted">
                                    Waiting for the artist to submit a transaction ID.
                                </p>

                            @endif

                        </div>


                        {{-- APPROVAL AND REJECTION --}}

                        <div class="action-area">

                            @if ($application->status === 'pending')

                                <h4>Review Application</h4>

                                <p class="muted">
                                    Review the artist's information before
                                    making a membership decision.
                                </p>


                                {{-- APPROVE APPLICATION --}}

                                <form method="POST"
                                      action="{{ route(
                                          'admin.memberships.approve',
                                          $application
                                      ) }}"
                                      class="action-form"
                                      onsubmit="return confirm('Approve this artist membership application?');">

                                    @csrf

                                    <label class="form-label"
                                           for="approve-notes-{{ $application->id }}">
                                        Admin notes (optional)
                                    </label>

                                    <textarea
                                        class="admin-notes"
                                        id="approve-notes-{{ $application->id }}"
                                        name="admin_notes"
                                        maxlength="2000"
                                        placeholder="Optional notes for this application..."
                                    ></textarea>

                                    <button type="submit"
                                            class="button button-primary">
                                        Approve Membership
                                    </button>

                                </form>


                                {{-- REJECT APPLICATION --}}

                                <form method="POST"
                                      action="{{ route(
                                          'admin.memberships.reject',
                                          $application
                                      ) }}"
                                      class="action-form"
                                      onsubmit="return confirm('Reject this artist membership application?');">

                                    @csrf

                                    <label class="form-label"
                                           for="reject-notes-{{ $application->id }}">
                                        Reason for rejection (required)
                                    </label>

                                    <textarea
                                        class="admin-notes"
                                        id="reject-notes-{{ $application->id }}"
                                        name="admin_notes"
                                        maxlength="2000"
                                        required
                                        placeholder="Explain why this application is being rejected..."
                                    ></textarea>

                                    <button type="submit"
                                            class="button button-danger">
                                        Reject Application
                                    </button>

                                </form>


                            @elseif ($application->status === 'approved')

                                <h4>Membership Approved</h4>

                                <p class="muted">
                                    This artist's membership application
                                    has been approved.

                                    @if ($application->payment_status === 'verified')
                                        Payment is verified. The artist meets
                                        the membership and payment requirements
                                        for Artist Studio access.
                                    @else
                                        Payment verification is still required
                                        before Artist Studio access is granted.
                                    @endif
                                </p>


                            @elseif ($application->status === 'rejected')

                                <h4>Application Rejected</h4>

                                <p class="muted">
                                    This application has already been rejected.
                                </p>

                            @endif


                            @if ($application->admin_notes)
                                <div class="detail-box">
                                    <div class="detail-label">Admin Notes</div>

                                    <div class="detail-value">
                                        {{ $application->admin_notes }}
                                    </div>
                                </div>
                            @endif

                        </div>

                    </section>

                @endforeach

            </div>

        @else

            <div class="empty-state">
                <h3>No Artist Applications Yet</h3>

                <p>
                    When an artist submits a membership application,
                    their information will appear here automatically.
                </p>
            </div>

        @endif


        {{-- RECENT MEMBERSHIP ACTIVITY --}}

        <h2 class="section-heading" id="recent-activity">
            Recent Membership Activity
        </h2>

        <div class="activity-panel">

            @if (isset($applications) && $applications->isNotEmpty())

                @foreach ($applications->take(5) as $application)

                    <div class="activity-item">

                        <div class="activity-heading">
                            <strong>
                                {{ $application->artist_name }}
                            </strong>

                            <span class="status status-{{ in_array(
                                $application->status ?? 'pending',
                                ['pending', 'approved', 'rejected']
                            ) ? $application->status : 'pending' }}">
                                {{ ucfirst($application->status ?? 'pending') }}
                            </span>
                        </div>

                        <div class="muted">
                            Application #{{ $application->id }}

                            · Membership:
                            {{ ucfirst($application->status ?? 'pending') }}

                            · Payment:
                            {{ ucfirst($application->payment_status ?? 'unpaid') }}
                        </div>

                        <div class="muted">
                            Submitted
                            {{ $application->created_at
                                ? $application->created_at->format('d M Y, h:i A')
                                : 'Date unavailable' }}
                        </div>

                    </div>

                @endforeach

            @else

                <p>
                    No membership activity is available yet.
                    New artist applications will appear here.
                </p>

            @endif

        </div>


        {{-- FOOTER --}}

        <div class="footer-note">
            Artfy Administration · Manage your gallery responsibly.
            <a href="{{ route('home') }}"
               class="portfolio-link">
                Return to Discover
            </a>
        </div>

    </main>
</div>
</body>
</html>
