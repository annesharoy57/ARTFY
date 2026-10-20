@extends('admin.layout')

@section('title', 'Artist Memberships | Artfy')
@section('page_eyebrow', 'Artfy / Membership Management')
@section('page_title', 'Artist Memberships')
@section('page_intro', 'Review artist applications and decide whether to approve or reject membership.')

@push('styles')
<style>
    .application {
        background: white;
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 22px;
        margin: 18px 0;
    }

    .app-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .app-head h2 {
        font-family: Georgia, serif;
        font-size: 22px;
        font-weight: normal;
        margin: 0 0 7px;
        overflow-wrap: anywhere;
    }

    .status {
        display: inline-block;
        border-radius: 20px;
        padding: 7px 10px;
        font-size: 11px;
        font-weight: bold;
        background: #f0eee4;
        color: var(--muted);
    }

    .status.pending {
        background: var(--warning);
        color: #805b00;
    }

    .status.approved,
    .status.verified {
        background: var(--success);
        color: #276738;
    }

    .status.rejected,
    .status.unpaid {
        background: var(--danger);
        color: #a33128;
    }

    .membership-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-top: 18px;
    }

    .detail {
        min-width: 0;
        background: #faf9f4;
        border: 1px solid var(--border);
        padding: 14px;
        border-radius: 10px;
    }

    .detail-label {
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--muted);
        margin-bottom: 8px;
    }

    .detail-value {
        font-size: 14px;
        line-height: 1.7;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .payment-panel {
        background: #f7f6ee;
        border: 1px solid var(--border);
        padding: 16px;
        border-radius: 10px;
        margin-top: 16px;
    }

    .payment-panel h3,
    .review-actions h3 {
        font-size: 16px;
        margin: 0 0 14px;
    }

    .payment-status-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .review-actions {
        border-top: 1px solid var(--border);
        padding-top: 18px;
        margin-top: 18px;
    }

    .review-actions form + form {
        margin-top: 18px;
    }

    .field-label {
        display: block;
        font-size: 12px;
        font-weight: bold;
        color: var(--muted);
        margin-top: 12px;
    }

    .review-textarea {
        width: 100%;
        min-height: 72px;
        resize: vertical;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 11px;
        font: inherit;
        font-size: 13px;
        margin: 7px 0 10px;
        color: var(--text);
        background: white;
    }

    .admin-notes {
        margin-top: 15px;
    }

    @media (max-width: 650px) {
        .membership-grid,
        .payment-status-grid {
            grid-template-columns: 1fr;
        }

        .application {
            padding: 17px;
        }
    }
</style>
@endpush

@section('content')

    @if (session('success'))
        <div class="alert success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert validation">
            <strong>Please check:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse ($applications as $application)
        <section class="application">
            <div class="app-head">
                <div>
                    <h2>{{ $application->artist_name }}</h2>

                    <div class="muted">
                        Application #{{ $application->id }}
                        ·
                        {{ $application->created_at?->format('d M Y, h:i A') ?? 'Date unavailable' }}
                    </div>

                    <div class="muted">
                        Account: {{ $application->user->name ?? 'Unknown user' }}
                        ·
                        {{ $application->user->email ?? '' }}
                    </div>
                </div>

                <span class="status {{ in_array($application->status, ['pending', 'approved', 'rejected']) ? $application->status : 'pending' }}">
                    {{ ucfirst($application->status ?? 'pending') }}
                </span>
            </div>

            @php
                $categories = $application->art_categories ?? [];

                if (is_string($categories)) {
                    $decoded = json_decode($categories, true);
                    $categories = is_array($decoded) ? $decoded : [];
                }

                if (empty($categories) && $application->art_category) {
                    $categories = [$application->art_category];
                }

                if (!is_array($categories)) {
                    $categories = [];
                }
            @endphp

            <div class="membership-grid">
                <div class="detail">
                    <div class="detail-label">Biography</div>
                    <div class="detail-value">
                        {{ $application->biography ?: 'Not provided' }}
                    </div>
                </div>

                <div class="detail">
                    <div class="detail-label">Art Categories</div>
                    <div class="detail-value">
                        {{ count($categories) ? implode(', ', $categories) : 'Not specified' }}
                    </div>
                </div>

                <div class="detail">
                    <div class="detail-label">Portfolio</div>
                    <div class="detail-value">
                        @if ($application->portfolio_url)
                            <a href="{{ $application->portfolio_url }}"
                               target="_blank"
                               rel="noopener noreferrer">
                                Open artist portfolio ↗
                            </a>
                        @else
                            Not provided
                        @endif
                    </div>
                </div>

                <div class="detail">
                    <div class="detail-label">Annual Membership Fee</div>
                    <div class="detail-value">
                        ৳{{ number_format((float) ($application->membership_fee ?? 1000)) }}
                        / year
                    </div>
                </div>
            </div>

            <div class="payment-panel">
                <h3>Payment Information</h3>

                <div class="payment-status-grid">
                    <div>
                        <div class="detail-label">Payment Status</div>

                        <span class="status {{ in_array($application->payment_status, ['pending', 'verified', 'unpaid']) ? $application->payment_status : 'unpaid' }}">
                            {{ ucfirst($application->payment_status ?? 'unpaid') }}
                        </span>
                    </div>

                    <div>
                        <div class="detail-label">Payment Method</div>
                        <div class="detail-value">
                            {{ $application->payment_method ? ucfirst($application->payment_method) : 'Manual' }}
                        </div>
                    </div>

                    <div>
                        <div class="detail-label">Transaction ID</div>
                        <div class="detail-value">
                            {{ $application->payment_reference ?: 'Not submitted' }}
                        </div>
                    </div>

                    <div>
                        <div class="detail-label">Payment Submitted</div>
                        <div class="detail-value">
                            {{ $application->payment_submitted_at?->format('d M Y, h:i A') ?? 'Not submitted' }}
                        </div>
                    </div>
                </div>

                @if ($application->payment_verified_at)
                    <p class="muted">
                        Verified on
                        {{ $application->payment_verified_at->format('d M Y, h:i A') }}
                    </p>
                @endif

                @if ($application->status === 'approved'
                    && $application->payment_status === 'pending'
                    && filled($application->payment_reference))

                    <form
                        method="POST"
                        action="{{ route('admin.memberships.verify-payment', $application) }}"
                        onsubmit="return confirm('Have you checked the transaction? Verify this payment now?');"
                    >
                        @csrf
                        <button class="btn primary" type="submit">
                            Verify Payment
                        </button>
                    </form>

                @elseif ($application->payment_status === 'verified')

                    <p class="muted">Payment verified.</p>

                @elseif ($application->status !== 'approved'
                    && $application->payment_reference)

                    <p class="muted">
                        Approve the membership before verifying its payment.
                    </p>

                @else

                    <p class="muted">
                        Waiting for a submitted transaction ID.
                    </p>

                @endif
            </div>

            <div class="review-actions">
                @if ($application->status === 'pending')
                    <h3>Review Application</h3>

                    <form
                        method="POST"
                        action="{{ route('admin.memberships.approve', $application) }}"
                        onsubmit="return confirm('Approve this artist membership application?');"
                    >
                        @csrf

                        <label class="field-label"
                               for="approve-{{ $application->id }}">
                            Admin notes (optional)
                        </label>

                        <textarea
                            id="approve-{{ $application->id }}"
                            class="review-textarea"
                            name="admin_notes"
                            maxlength="2000"
                            placeholder="Optional notes..."
                        ></textarea>

                        <button class="btn primary" type="submit">
                            Approve Membership
                        </button>
                    </form>

                    <form
                        method="POST"
                        action="{{ route('admin.memberships.reject', $application) }}"
                        onsubmit="return confirm('Reject this artist membership application?');"
                    >
                        @csrf

                        <label class="field-label"
                               for="reject-{{ $application->id }}">
                            Reason for rejection (required)
                        </label>

                        <textarea
                            id="reject-{{ $application->id }}"
                            class="review-textarea"
                            name="admin_notes"
                            maxlength="2000"
                            required
                            placeholder="Explain why this application is being rejected..."
                        ></textarea>

                        <button class="btn danger" type="submit">
                            Reject Application
                        </button>
                    </form>

                @elseif ($application->status === 'approved')

                    <h3>Membership Approved</h3>

                    <p class="muted">
                        Payment must also be verified before Artist Studio access is granted.
                    </p>

                @else

                    <h3>Application Rejected</h3>

                @endif

                @if ($application->admin_notes)
                    <div class="detail admin-notes">
                        <div class="detail-label">Admin Notes</div>
                        <div class="detail-value">
                            {{ $application->admin_notes }}
                        </div>
                    </div>
                @endif
            </div>
        </section>

    @empty

        <div class="empty">
            <h2>No Artist Applications Yet</h2>
            <p class="muted">
                New artist applications will appear here.
            </p>
        </div>

    @endforelse

    <div class="pagination">
        {{ $applications->links() }}
    </div>

@endsection
