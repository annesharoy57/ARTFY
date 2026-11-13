<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#414c2c">
    <link rel="icon" type="image/svg+xml" href="{{ asset('artfy-logo.svg') }}?v=3">

    <title>Artist Membership Application | Artfy</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/artfy.css') }}?v=20261022-membership-payment">

    <style>
        .artist-application-page {
            min-height: 100vh;
            padding: 60px 20px;
            background: #f5f3e8;
            color: #414c2c;
        }

        .application-container {
            max-width: 760px;
            margin: 0 auto;
        }

        .application-back {
            display: inline-block;
            margin-bottom: 28px;
            color: #55613a;
            text-decoration: none;
            font-weight: 600;
        }

        .application-card {
            padding: 38px;
            background: #fffef8;
            border: 1px solid #dedfce;
            border-radius: 20px;
            box-shadow: 0 12px 35px rgba(65, 76, 44, 0.08);
        }

        .application-eyebrow {
            color: #72795f;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .application-card h1 {
            margin: 12px 0;
            color: #414c2c;
            font-size: clamp(28px, 5vw, 40px);
            line-height: 1.2;
        }

        .application-description {
            margin-bottom: 28px;
            color: #72795f;
            line-height: 1.7;
        }

        .application-field {
            margin-bottom: 21px;
        }

        .application-field > label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .application-field input:not([type="checkbox"]),
        .application-field textarea,
        .application-field select {
            width: 100%;
            box-sizing: border-box;
            padding: 13px 14px;
            border: 1px solid #cdd1bd;
            border-radius: 9px;
            background: #fff;
            color: #414c2c;
            font: inherit;
        }

        .application-field textarea {
            min-height: 130px;
            resize: vertical;
        }

        .application-field input:focus,
        .application-field textarea:focus,
        .application-field select:focus {
            outline: 2px solid #72795f;
            outline-offset: 2px;
        }

        .application-help {
            display: block;
            margin-top: 6px;
            color: #72795f;
            font-size: 13px;
        }

        .application-button {
            display: inline-block;
            width: 100%;
            box-sizing: border-box;
            padding: 14px 20px;
            border: 0;
            border-radius: 9px;
            background: #55613a;
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .application-button:hover {
            background: #414c2c;
        }

        .application-button:disabled {
            cursor: not-allowed;
            opacity: 0.65;
        }

        .application-alert {
            margin-bottom: 22px;
            padding: 14px 16px;
            border-radius: 9px;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .application-success,
        .payment-notice {
            background: #e8efdf;
            color: #314322;
        }

        .application-error,
        .payment-warning {
            background: #f8e6e1;
            color: #8b2f22;
        }

        .application-status {
            margin: 24px 0;
            padding: 20px;
            border-radius: 12px;
            background: #f0f0e5;
        }

        .application-status p {
            margin: 8px 0;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .application-status strong {
            color: #414c2c;
        }

        .application-status a {
            display: inline-block;
            margin-top: 10px;
            color: #55613a;
            font-weight: 700;
        }

        .membership-fee-card {
            margin-top: 24px;
            padding: 24px;
            border: 1px solid #dedfce;
            border-radius: 12px;
            background: #fffef8;
        }

        .membership-fee-card h2 {
            margin-top: 0;
            color: #414c2c;
        }

        .membership-fee {
            margin: 12px 0;
            color: #414c2c;
            font-size: 30px;
            font-weight: 700;
        }

        .membership-fee-card p {
            line-height: 1.7;
        }

        .payment-form {
            margin-top: 20px;
        }

        .payment-notice,
        .payment-warning {
            padding: 13px;
            border-radius: 9px;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .payment-method-note {
            margin: 16px 0;
            padding: 14px;
            background: #f0f0e5;
            border-radius: 9px;
            line-height: 1.7;
        }

        .application-footer {
            margin-top: 24px;
            text-align: center;
        }

        .application-footer a {
            color: #55613a;
            font-weight: 600;
            text-decoration: none;
        }

        /* Branded artist membership experience */
        body { background: radial-gradient(circle at 8% 8%, #e7eadb, transparent 30%), radial-gradient(circle at 94% 72%, #eee4cc, transparent 32%), #f5f3e8; }
        .artist-application-page { padding: 25px 24px 70px; background: transparent; }
        .application-container { max-width: 1060px; }
        .application-topbar { display:flex; align-items:center; justify-content:space-between; gap:20px; margin:0 0 24px; padding:10px 2px; }
        .application-brand { display:inline-flex; align-items:center; gap:10px; color:#414c2c; font:700 27px Georgia,serif; text-decoration:none; }
        .application-brand img { width:42px; height:42px; filter:drop-shadow(0 4px 8px rgba(65,76,44,.16)); }
        .application-brand span span { color:#788456; }
        .application-back { display:inline-flex; align-items:center; gap:9px; margin:0; padding:10px 14px; border:1px solid #dddccb; border-radius:30px; background:rgba(255,254,248,.75); color:#55613a; font:600 12px "DM Sans",Arial,sans-serif; text-decoration:none; cursor:pointer; transition:background .2s,transform .2s; }
        .application-back:hover { transform:translateY(-1px); background:#fffef8; }
        .application-back-form { margin:0; }
        .application-card { overflow:hidden; padding:0; border:1px solid rgba(125,132,92,.24); border-radius:25px; background:rgba(255,254,248,.94); box-shadow:0 30px 75px rgba(65,76,44,.12); }
        .application-heading { position:relative; isolation:isolate; overflow:hidden; display:grid; grid-template-columns:minmax(0,1.3fr) minmax(210px,.7fr); align-items:center; gap:28px; padding:36px 42px; background:linear-gradient(115deg,#414c2c,#586640 72%,#6d7850); color:#fffdf5; }
        .application-heading:after { position:absolute; z-index:-1; right:8%; top:-115px; width:300px; height:300px; border:1px solid rgba(255,255,255,.15); border-radius:50%; content:""; box-shadow:0 0 0 20px rgba(255,255,255,.045),0 0 0 48px rgba(255,255,255,.035); }
        .application-heading .application-eyebrow { color:#e2d6b4; font-size:9px; letter-spacing:1.8px; }
        .application-heading h1 { margin:10px 0 10px; color:#fffdf5; font:500 clamp(31px,4.3vw,46px)/1.06 "Playfair Display",Georgia,serif; letter-spacing:-.03em; }
        .application-heading .application-description { max-width:640px; margin:0; color:#e3e4d9; font-size:13px; line-height:1.75; }
        .application-art-note { position:relative; z-index:1; justify-self:end; width:min(100%,245px); padding:20px; border:1px solid rgba(255,255,255,.25); border-radius:16px; background:rgba(255,255,255,.09); backdrop-filter:blur(8px); }
        .application-art-note span { color:#e3d6b2; font-size:9px; font-weight:700; letter-spacing:1.6px; }
        .application-art-note strong { display:block; margin:9px 0 5px; color:#fffdf5; font:italic 31px Georgia,serif; }
        .application-art-note p { margin:0; color:#e1e3d7; font-size:11px; line-height:1.6; }
        .application-body { padding:30px 42px 36px; }
        .application-alert { border:1px solid transparent; border-radius:12px; }
        .application-success,.payment-notice { border-color:#d8e3c9; background:#f0f5e9; color:#40552f; }
        .application-error,.payment-warning { border-color:#efd4cc; background:#fff3ef; color:#8b3e31; }
        .application-status,.membership-fee-card { border:1px solid #e8e5d8; border-radius:17px; background:linear-gradient(145deg,#fffefa,#f7f6ee); }
        .application-status { padding:22px; }
        .application-status h2,.membership-fee-card h2 { margin:0 0 12px; color:#3e4a31; font:500 23px "Playfair Display",Georgia,serif; }
        .application-status p,.membership-fee-card p { color:#6f7562; }
        .membership-fee-card { margin:0 0 26px; padding:22px 24px; }
        .membership-fee { color:#414c2c; font:600 32px "Playfair Display",Georgia,serif; }
        .application-field { margin-bottom:20px; }
        .application-field > label { color:#4c5540; font-size:12px; letter-spacing:.015em; }
        .application-field input:not([type="checkbox"]),.application-field textarea,.application-field select { border-color:#d8d9c9; border-radius:11px; background:#fffefa; box-shadow:inset 0 1px 3px rgba(65,76,44,.035); transition:border-color .2s,box-shadow .2s; }
        .application-field input:focus,.application-field textarea:focus,.application-field select:focus { outline:3px solid rgba(151,160,113,.25); outline-offset:1px; border-color:#87906b; }
        .application-help { color:#858675; font-size:11px; line-height:1.6; }
        .category-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:9px; margin-top:10px; }
        .category-option { display:flex; align-items:center; gap:10px; min-height:46px; margin:0; padding:10px 12px; border:1px solid #e6e4d8; border-radius:11px; background:#fffefa; color:#555b49; font-size:12px; cursor:pointer; transition:border-color .18s,background .18s,transform .18s; }
        .category-option:hover { transform:translateY(-1px); border-color:#c5caaa; background:#f8f8f1; }
        .category-option input { width:16px; height:16px; margin:0; accent-color:#596b43; }
        .application-button { border-radius:11px; background:linear-gradient(110deg,#788456,#596b43); box-shadow:0 9px 19px rgba(85,97,58,.18); transition:transform .2s,box-shadow .2s,background .2s; }
        .application-button:hover { transform:translateY(-2px); background:linear-gradient(110deg,#697849,#4d603b); box-shadow:0 13px 24px rgba(85,97,58,.23); }
        .payment-method-note { border:1px solid #e6e4d8; border-radius:12px; background:#f7f6ee; color:#626957; }
        .application-footer { padding:19px 0 0; }
        .application-footer a { text-underline-offset:3px; }
        .application-footer span { color:#858675; font-size:11px; }

        @media (max-width: 600px) {
            .artist-application-page {
                padding: 15px 14px 45px;
            }

            .application-card {
                padding: 0;
            }

            .membership-fee-card {
                padding: 20px 16px;
            }
        }

        @media(max-width:720px){.application-heading{grid-template-columns:1fr;padding:29px 25px}.application-art-note{justify-self:start;width:100%;max-width:none}.application-body{padding:24px 25px 28px}}
        @media(max-width:460px){.application-topbar{align-items:flex-start}.application-brand{font-size:23px}.application-brand img{width:36px;height:36px}.application-back{padding:9px 11px;font-size:11px}.application-heading{padding:25px 20px}.application-body{padding:20px 18px 23px}.category-grid{grid-template-columns:1fr}.application-heading h1{font-size:34px}}
        @media(prefers-reduced-motion:reduce){.application-button,.application-back,.category-option,.application-field input:not([type="checkbox"]){transition:none}}
    </style>
</head>

<body>
<main class="artist-application-page">
    <div class="application-container">
        @php
            $artistApplication = auth()->user()?->artistApplication;
            $artistMembershipApproved = $artistApplication
                && $artistApplication->status === 'approved'
                && $artistApplication->payment_status === 'verified'
                && in_array(auth()->user()->membership_status, ['active', 'approved'], true);
        @endphp

        <header class="application-topbar">
            <a href="{{ $artistMembershipApproved ? route('home') : route('artist.application') }}" class="application-brand" aria-label="Artfy home">
                <img src="{{ asset('artfy-logo.svg') }}" alt="">
                <span>Art<span>fy.</span></span>
            </a>
            @if ($artistMembershipApproved)
                <a href="{{ route('home') }}" class="application-back"><span aria-hidden="true">←</span> Back to Artfy</a>
            @else
                <form method="POST" action="{{ route('logout') }}" class="application-back-form">
                    @csrf
                    <button type="submit" class="application-back"><span aria-hidden="true">↗</span> Log out &amp; return to Discover</button>
                </form>
            @endif
        </header>

        <section class="application-card">
            <div class="application-heading">
                <div>
                    <span class="application-eyebrow">ARTFY ARTIST MEMBERSHIP</span>
                    <h1>Share your art<br>with the world.</h1>
                    <p class="application-description">Submit your artist membership application and pay the annual membership fee through SSLCOMMERZ. Your application must be approved by the Admin before Artist Studio access is activated. Your one-year membership period starts on the approval date.</p>
                </div>
                <aside class="application-art-note"><span>YOUR CREATIVE JOURNEY</span><strong>Make it yours.</strong><p>A thoughtful space for artists, original work, and new perspectives.</p></aside>
            </div>
            <div class="application-body">

            @if (session('success'))
                <div class="application-alert application-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="application-alert application-error">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="application-alert application-error">
                    <strong>Please correct the following:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- EXISTING APPLICATION --}}
            @if ($application)

                <div class="application-status">
                    <h2>Application Status</h2>

                    <p>
                        <strong>Artist name:</strong>
                        {{ $application->artist_name }}
                    </p>

                    <p>
                        <strong>Art categories:</strong>
                        {{ count($application->art_categories ?? []) > 0
                            ? implode(', ', $application->art_categories)
                            : ($application->art_category ?? 'Not specified') }}
                    </p>

                    <p>
                        <strong>Membership review:</strong>
                        {{ ucfirst($application->status) }}
                    </p>

                    <p>
                        <strong>Payment status:</strong>
                        {{ ucfirst($application->payment_status) }}
                    </p>

                    @if ($application->payment_reference)
                        <p>
                            <strong>Payment reference:</strong>
                            {{ $application->payment_reference }}
                        </p>
                    @endif

                    @if ($application->admin_notes)
                        <p>
                            <strong>Admin feedback:</strong>
                            {{ $application->admin_notes }}
                        </p>
                    @endif

                    @if ($application->status === 'rejected')

                        <p class="payment-warning">
                            Your application was rejected. Please review
                            the Admin feedback or contact the Admin for
                            clarification. Payment is unavailable for a
                            rejected application.
                        </p>

                    @elseif (
                        $application->status === 'approved' &&
                        $application->payment_status === 'verified'
                    )

                        @if (
                            auth()->user()->membership_status === 'active' &&
                            auth()->user()->membership_expires_at &&
                            auth()->user()->membership_expires_at->isFuture()
                        )
                            <p class="payment-notice">
                                Your payment has been verified and your
                                membership is active.
                            </p>

                            <p>
                                <strong>Membership expires:</strong>
                                {{ auth()->user()->membership_expires_at->format('d M Y') }}
                            </p>

                            <a href="{{ route('artist.studio') }}">
                                Go to Artist Studio &rarr;
                            </a>
                        @else
                            <p class="payment-notice">
                                Your payment has been verified and your
                                application is approved. Your membership
                                is awaiting activation or renewal.
                            </p>
                        @endif

                    @elseif (
                        $application->status === 'pending' &&
                        $application->payment_status === 'verified'
                    )

                        <p class="payment-notice">
                            Your payment has been verified. Your application
                            is awaiting Admin approval. Artist Studio access
                            will be available after approval.
                        </p>

                    @elseif ($application->status === 'pending')

                        <p>
                            Your application is awaiting Admin review.
                            You can pay the annual membership fee now.
                            The Admin must verify your application and payment
                            before membership is activated.
                        </p>

                    @elseif ($application->status === 'approved')

                        <p>
                            Your application has been approved. Complete
                            your membership payment to proceed with activation.
                        </p>

                    @endif
                </div>

                {{-- MEMBERSHIP PAYMENT --}}
                <div class="membership-fee-card">
                    <h2>Artist Membership Payment</h2>

                    <p>Annual membership fee</p>

                    <div class="membership-fee">
                        ৳{{ number_format($application->membership_fee ?? 1000) }}
                        <span style="font-size: 15px; font-weight: 400;">
                            / year
                        </span>
                    </div>

                    @if ($application->status === 'rejected')

                        <p class="payment-warning">
                            Payment is unavailable because your application
                            has been rejected.
                        </p>

                    @elseif ($application->payment_status === 'verified')

                        <p class="payment-notice">
                            Your membership payment has been verified.
                            @if ($application->status === 'pending')
                                Please wait for Admin approval before
                                Artist Studio access is activated.
                            @elseif ($application->status === 'approved')
                                @if (
                                    auth()->user()->membership_status === 'active' &&
                                    auth()->user()->membership_expires_at &&
                                    auth()->user()->membership_expires_at->isFuture()
                                )
                                    Your membership is active. Thank you
                                    for supporting Artfy.
                                @else
                                    Your application is approved. If access
                                    is not active, please contact the Admin.
                                @endif
                            @endif
                        </p>

                    @elseif ($application->payment_status === 'pending')

                        <p class="payment-notice">
                            Your payment is awaiting confirmation from
                            SSLCOMMERZ. This status does not mean the payment
                            has been verified.
                        </p>

                        <div class="payment-method-note">
                            If you left checkout before completing payment,
                            you can return to the gateway below. If you may
                            already have been charged, check with Artfy
                            support before retrying.
                        </div>

                        <form
                            action="{{ route('artist.application.payment.store') }}"
                            method="POST"
                            class="payment-form"
                        >
                            @csrf

                            <button type="submit" class="application-button">
                                Continue to SSLCOMMERZ Checkout
                            </button>
                        </form>

                    @elseif (in_array($application->status, ['pending', 'approved'], true))

                        <p>
                            Pay your annual membership fee securely through
                            SSLCOMMERZ. Payment verification alone does not
                            activate your membership.
                        </p>

                        <div class="payment-method-note">
                            After payment, Artfy will verify the transaction
                            with SSLCOMMERZ. Your Artist Studio access will
                            be activated only after Admin approval.
                        </div>

                        <form
                            action="{{ route('artist.application.payment.store') }}"
                            method="POST"
                            class="payment-form"
                        >
                            @csrf

                            <button type="submit" class="application-button">
                                Pay ৳{{ number_format($application->membership_fee ?? 1000) }}
                                with SSLCOMMERZ
                            </button>
                        </form>

                    @else

                        <p class="payment-warning">
                            Your application status does not currently allow
                            payment. Please contact the Admin.
                        </p>

                    @endif
                </div>

            @else

                {{-- NEW APPLICATION --}}
                <div class="membership-fee-card">
                    <h2>Annual Artist Membership</h2>

                    <p>Annual membership fee</p>

                    <div class="membership-fee">
                        ৳{{ number_format(config('services.artfy_membership.annual_fee', 1000)) }}
                        <span style="font-size: 15px; font-weight: 400;">
                            / year
                        </span>
                    </div>

                    <p>
                        Submit your application and then pay through
                        SSLCOMMERZ. Your application will be reviewed by
                        the Admin. If approved, your one-year membership
                        starts on the approval date.
                    </p>
                </div>

                <form
                    action="{{ route('artist.application.submit') }}"
                    method="POST"
                >
                    @csrf

                    <div class="application-field">
                        <label for="artist_name">
                            Artist or Professional Name *
                        </label>

                        <input
                            type="text"
                            id="artist_name"
                            name="artist_name"
                            value="{{ old('artist_name') }}"
                            maxlength="255"
                            required
                        >
                    </div>

                    <div class="application-field">
                        <label for="biography">
                            About You and Your Art *
                        </label>

                        <textarea
                            id="biography"
                            name="biography"
                            maxlength="5000"
                            required
                        >{{ old('biography') }}</textarea>

                        <small class="application-help">
                            Describe your artistic background, style,
                            experience, and creative interests.
                        </small>
                    </div>

                    <div class="application-field">
                        <label>
                            Art Categories * (Select all that apply)
                        </label>

                        @php
                            $categories = [
                                'Painting',
                                'Photography',
                                'Digital Art',
                                'Illustration',
                                'Sculpture',
                                'Mixed Media',
                                'Drawing',
                                'Printmaking',
                                'Crafts',
                                'Other',
                            ];

                            $selectedCategories = old('art_categories', []);
                        @endphp

                        <div class="category-grid">
                        @foreach ($categories as $category)
                            <label class="category-option">
                                <input
                                    type="checkbox"
                                    name="art_categories[]"
                                    value="{{ $category }}"
                                    @checked(in_array($category, $selectedCategories))
                                >
                                {{ $category }}
                            </label>
                        @endforeach
                        </div>
                    </div>

                    <div class="application-field">
                        <label for="portfolio_url">
                            Portfolio Link (Optional)
                        </label>

                        <input
                            type="url"
                            id="portfolio_url"
                            name="portfolio_url"
                            value="{{ old('portfolio_url') }}"
                            maxlength="255"
                            placeholder="https://example.com/your-portfolio"
                        >

                        <small class="application-help">
                            Add a link to your portfolio or existing art profile.
                        </small>
                    </div>

                    <button type="submit" class="application-button">
                        Submit Membership Application
                    </button>
                </form>

            @endif

            </div>
        </section>

        <div class="application-footer">
            @if ($artistMembershipApproved)
                <a href="{{ route('home') }}">Return to Discover</a>
            @else
                <span>Artist account access unlocks after payment verification and Admin approval.</span>
            @endif
        </div>

    </div>
</main>
</body>
</html>
