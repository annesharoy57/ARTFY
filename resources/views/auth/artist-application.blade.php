<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Artist Membership Application | Artfy</title>

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

        @media (max-width: 600px) {
            .artist-application-page {
                padding: 30px 14px;
            }

            .application-card {
                padding: 24px 18px;
            }

            .membership-fee-card {
                padding: 20px 16px;
            }
        }
    </style>
</head>

<body>
<main class="artist-application-page">
    <div class="application-container">

        <a href="{{ route('home') }}" class="application-back">
            &larr; Back to Artfy
        </a>

        <section class="application-card">

            <span class="application-eyebrow">
                Artfy Artist Membership
            </span>

            <h1>Share your art with the world.</h1>

            <p class="application-description">
                Submit your artist membership application and pay the annual
                membership fee through SSLCOMMERZ. Your application must be
                approved by the Admin before Artist Studio access is activated.
                Your one-year membership period starts on the approval date.
            </p>

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

                        @foreach ($categories as $category)
                            <label style="display: flex; align-items: center; gap: 10px; margin: 12px 0; font-weight: 400;">
                                <input
                                    type="checkbox"
                                    name="art_categories[]"
                                    value="{{ $category }}"
                                    style="width: auto;"
                                    @checked(in_array($category, $selectedCategories))
                                >
                                {{ $category }}
                            </label>
                        @endforeach
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

        </section>

        <div class="application-footer">
            <a href="{{ route('home') }}">
                Return to Discover
            </a>
        </div>

    </div>
</main>
</body>
</html>
