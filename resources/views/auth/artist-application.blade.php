<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Artist Membership Application | Artfy</title>

    <link rel="stylesheet" href="{{ asset('css/artfy.css') }}?v=20261016-payment">

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

        .application-field label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .application-field input,
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
        }

        .application-button:hover {
            background: #414c2c;
        }

        .application-alert {
            margin-bottom: 22px;
            padding: 14px 16px;
            border-radius: 9px;
            line-height: 1.6;
        }

        .application-success {
            background: #e8efdf;
            color: #314322;
        }

        .application-error {
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

        .payment-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .payment-form input {
            display: block;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 16px;
            padding: 13px 14px;
            border: 1px solid #cdd1bd;
            border-radius: 9px;
            background: #fff;
            color: #414c2c;
            font: inherit;
        }

        .payment-notice {
            padding: 13px;
            border-radius: 9px;
            background: #e8efdf;
            color: #314322;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .payment-warning {
            padding: 13px;
            border-radius: 9px;
            background: #f8e6e1;
            color: #8b2f22;
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
                    Complete your artist membership application.
                    Our Admin team will review your information.
                    Artist Studio access becomes available after membership
                    approval and payment verification.
                </p>

                {{-- Success message --}}
                @if (session('success'))
                    <div class="application-alert application-success">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- General error message --}}
                @if (session('error'))
                    <div class="application-alert application-error">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Validation errors --}}
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
                                : $application->art_category }}
                        </p>

                        <p>
                            <strong>Membership review:</strong>
                            {{ ucfirst($application->status) }}
                        </p>

                        <p>
                            <strong>Payment verification:</strong>
                            {{ ucfirst($application->payment_status) }}
                        </p>

                        @if ($application->admin_notes)
                            <p>
                                <strong>Admin feedback:</strong>
                                {{ $application->admin_notes }}
                            </p>
                        @endif

                        @if (
                            $application->status === 'approved' &&
                            $application->payment_status === 'verified'
                        )
                            <p class="payment-notice">
                                Your membership and payment have both been verified.
                                You can now access Artist Studio.
                            </p>

                            <a href="{{ route('artist.studio') }}">
                                Go to Artist Studio &rarr;
                            </a>
                        @else
                            <p>
                                Your application is being processed.
                                You can check this page for updates.
                            </p>
                        @endif
                    </div>

                    {{-- MEMBERSHIP PAYMENT SECTION --}}
                    <div class="membership-fee-card">

                        <h2>Artist Membership Payment</h2>

                        <p>Annual membership fee</p>

                        <div class="membership-fee">
                            ৳{{ number_format($application->membership_fee ?? 1000) }}
                            <span style="font-size: 15px; font-weight: 400;">
                                / year
                            </span>
                        </div>

                        @if ($application->payment_status === 'verified')

                            <p class="payment-notice">
                                Your payment has been verified by the Admin.
                                Thank you for supporting Artfy.
                            </p>

                        @elseif ($application->payment_status === 'pending')

                            <p class="payment-notice">
                                Your transaction ID has been submitted.
                                The Admin is reviewing your payment.
                            </p>

                            <p>
                                <strong>Submitted transaction ID:</strong><br>
                                {{ $application->payment_reference ?: 'Not available' }}
                            </p>

                        @else

                            @if ($application->status === 'rejected')
                                <p class="payment-warning">
                                    Your membership application has been rejected.
                                    Please contact the Admin for clarification
                                    before making any payment.
                                </p>
                            @else
                                <p>
                                    Submit your transaction ID after making your
                                    membership payment using the payment details
                                    provided by the Artfy Admin.
                                </p>

                                <p class="payment-warning">
                                    Payment receiver details have not yet been
                                    displayed on this page. Do not send money
                                    until the official payment instructions are
                                    provided.
                                </p>

                                <form
                                    action="{{ route('artist.application.payment') }}"
                                    method="POST"
                                    class="payment-form"
                                >
                                    @csrf

                                    <label for="payment_reference">
                                        Payment Transaction ID
                                    </label>

                                    <input
                                        type="text"
                                        id="payment_reference"
                                        name="payment_reference"
                                        value="{{ old('payment_reference') }}"
                                        maxlength="255"
                                        placeholder="Enter your transaction ID"
                                        required
                                    >

                                    <button
                                        type="submit"
                                        class="application-button"
                                    >
                                        Submit Transaction ID
                                    </button>
                                </form>
                            @endif

                        @endif
                    </div>

                @else

                    {{-- NEW APPLICATION FORM --}}
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
                            Your application will be reviewed by the Admin.
                            Submit payment details separately when the official
                            payment instructions are available.
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

                        <button
                            type="submit"
                            class="application-button"
                        >
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
