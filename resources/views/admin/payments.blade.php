@extends('admin.layout')

@section('title', 'Payment Verification | Artfy')

@section('page_eyebrow', 'Artfy / Finance')

@section('page_title', 'Payment Verification')

@section('page_intro', 'Check each transaction ID manually before marking a membership payment as verified.')

@push('styles')
<style>
    /* Payment verification page only.
       Sidebar and notification bell come from admin.layout. */

    .payment-alert {
        padding: 13px 16px;
        border-radius: 9px;
        margin-bottom: 14px;
        font-size: 14px;
        line-height: 1.7;
    }

    .payment-alert.success {
        background: var(--success);
        color: #276738;
        border: 1px solid #c9e5cb;
    }

    .payment-alert.error {
        background: var(--danger);
        color: #a33128;
        border: 1px solid #f0cbc7;
    }

    .payment-alert.validation {
        background: var(--warning);
        color: #805b00;
        border: 1px solid #f0dfae;
    }

    .payment-alert ul {
        margin: 8px 0 0;
        padding-left: 22px;
    }

    /* Payment application card */

    .payment-card {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 24px;
        margin: 20px 0;
        min-width: 0;
        transition: box-shadow .2s ease, transform .2s ease;
    }

    .payment-card:hover {
        box-shadow: 0 8px 22px rgba(65, 76, 44, .06);
    }

    .payment-card-heading {
        margin: 0 0 8px;
        color: var(--dark-olive);
        font-family: Georgia, serif;
        font-size: 23px;
        font-weight: normal;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .payment-meta {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.8;
        overflow-wrap: anywhere;
        margin: 5px 0;
    }

    /* Transaction details */

    .payment-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin: 22px 0;
    }

    .payment-detail {
        min-width: 0;
        background: #faf9f4;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 15px;
    }

    .payment-detail-label {
        color: var(--muted);
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        line-height: 1.6;
        margin-bottom: 8px;
    }

    .payment-detail-value {
        color: var(--dark-olive);
        font-size: 14px;
        line-height: 1.7;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .payment-reference {
        font-weight: bold;
    }

    /* Payment status */

    .payment-status {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 30px;
        background: var(--warning);
        color: #805b00;
        font-size: 11px;
        font-weight: bold;
        line-height: 1.5;
    }

    .payment-instructions {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.8;
        margin: 16px 0 20px;
    }

    /* Verification button */

    .payment-verify-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        max-width: 100%;
        padding: 12px 17px;
        border: 0;
        border-radius: 8px;
        background: var(--olive);
        color: var(--white);
        font-family: inherit;
        font-size: 13px;
        font-weight: bold;
        line-height: 1.5;
        text-align: center;
        overflow-wrap: anywhere;
        cursor: pointer;
        transition: background .2s ease;
    }

    .payment-verify-button:hover {
        background: var(--dark-olive);
    }

    .payment-verify-button:focus-visible {
        outline: 2px solid var(--olive);
        outline-offset: 3px;
    }

    /* Empty state */

    .payment-empty {
        padding: 42px 24px;
        text-align: center;
        background: var(--white);
        border: 1px dashed #c8c9b7;
        border-radius: 14px;
        margin: 20px 0;
    }

    .payment-empty h2 {
        color: var(--dark-olive);
        font-family: Georgia, serif;
        font-size: 24px;
        font-weight: normal;
        margin: 0 0 12px;
    }

    .payment-empty p {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.8;
        margin: 0 0 18px;
    }

    .payment-empty a {
        color: var(--olive);
        font-size: 13px;
        font-weight: bold;
        text-decoration: none;
    }

    .payment-empty a:hover {
        text-decoration: underline;
    }

    /* Pagination */

    .payment-pagination {
        margin-top: 24px;
        overflow-x: auto;
    }

    .payment-pagination nav {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 12px;
    }

    /* Responsive */

    @media (max-width: 650px) {
        .payment-card {
            padding: 18px;
        }

        .payment-card-heading {
            font-size: 21px;
        }

        .payment-detail-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .payment-verify-button {
            width: 100%;
        }

        .payment-empty {
            padding: 30px 18px;
        }
    }
</style>
@endpush

@section('content')

    {{-- SUCCESS MESSAGE --}}

    @if (session('success'))
        <div class="payment-alert success" role="status">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR MESSAGE --}}

    @if (session('error'))
        <div class="payment-alert error" role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- VALIDATION ERRORS --}}

    @if ($errors->any())
        <div class="payment-alert validation" role="alert">
            <strong>Please check the following:</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- PAYMENT APPLICATIONS --}}

    @forelse ($applications as $application)

        <section class="payment-card"
                 aria-labelledby="payment-application-{{ $application->id }}">

            {{-- Artist and application information --}}

            <h2 class="payment-card-heading"
                id="payment-application-{{ $application->id }}">
                {{ $application->artist_name }}
            </h2>

            <p class="payment-meta">
                Application #{{ $application->id }}
                · Submitted
                {{ $application->created_at?->format('d M Y, h:i A') ?? 'Date unavailable' }}
            </p>

            <p class="payment-meta">
                Account:
                {{ $application->user->name ?? 'Unknown user' }}

                @if ($application->user?->email)
                    · {{ $application->user->email }}
                @endif
            </p>


            {{-- Payment details --}}

            <div class="payment-detail-grid">

                <div class="payment-detail">
                    <div class="payment-detail-label">
                        Transaction ID
                    </div>

                    <div class="payment-detail-value payment-reference">
                        {{ $application->payment_reference ?: 'Not provided' }}
                    </div>
                </div>


                <div class="payment-detail">
                    <div class="payment-detail-label">
                        Payment Method
                    </div>

                    <div class="payment-detail-value">
                        {{ $application->payment_method
                            ? ucfirst($application->payment_method)
                            : 'Manual' }}
                    </div>
                </div>


                <div class="payment-detail">
                    <div class="payment-detail-label">
                        Membership Fee
                    </div>

                    <div class="payment-detail-value">
                        ৳{{ number_format((float) ($application->membership_fee ?? 1000), 2) }}
                    </div>
                </div>


                <div class="payment-detail">
                    <div class="payment-detail-label">
                        Payment Submitted
                    </div>

                    <div class="payment-detail-value">
                        {{ $application->payment_submitted_at?->format('d M Y, h:i A') ?? 'Date unavailable' }}
                    </div>
                </div>

            </div>


            {{-- Status and verification instructions --}}

            <p>
                <span class="payment-status">
                    Membership approved · Payment pending
                </span>
            </p>

            <p class="payment-instructions">
                Before verifying, check the transaction in the relevant payment
                service using the submitted transaction ID. Only verify the
                payment after independently confirming that it was received.
            </p>


            {{-- Existing payment verification route --}}

            <form
                method="POST"
                action="{{ route('admin.memberships.verify-payment', $application) }}"
                onsubmit="return confirm('Have you independently checked this transaction and confirmed that the payment was received?');"
            >
                @csrf

                <button class="payment-verify-button" type="submit">
                    Confirm &amp; Verify Payment
                </button>
            </form>

        </section>

    @empty

        {{-- No payments waiting for verification --}}

        <section class="payment-empty">

            <h2>No Payments Awaiting Verification</h2>

            <p>
                Payments appear here after a membership is approved and the
                artist submits a transaction ID.
            </p>

            <a href="{{ route('admin.memberships.index') }}">
                View Artist Memberships →
            </a>

        </section>

    @endforelse


    {{-- PAGINATION --}}

    @if ($applications->hasPages())
        <div class="payment-pagination">
            {{ $applications->links() }}
        </div>
    @endif

@endsection
