@extends('admin.layout')

@section('title', 'Reports & Activity | Artfy')

@section('page_eyebrow', 'Artfy / Overview')

@section('page_title', 'Reports & Activity')

@section('page_intro', 'A summary of artist membership applications and recorded payment statuses.')

@push('styles')
<style>
    .report-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .report-stat {
        min-width: 0;
        padding: 22px;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px;
    }

    .report-stat-label {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .report-stat-number {
        margin-top: 12px;
        color: var(--text);
        font-size: 30px;
        font-weight: 700;
        line-height: 1.2;
    }

    .report-section-title {
        margin: 32px 0 8px;
        color: var(--text);
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 23px;
        font-weight: 600;
    }

    .report-muted {
        margin: 0 0 16px;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.7;
    }

    .report-table-wrap {
        overflow-x: auto;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px;
    }

    .report-table {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
    }

    .report-table th,
    .report-table td {
        padding: 15px 16px;
        border-bottom: 1px solid var(--border);
        text-align: left;
        vertical-align: top;
        font-size: 13px;
    }

    .report-table th {
        background: var(--cream);
        color: var(--dark-olive);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .4px;
        white-space: nowrap;
    }

    .report-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .report-table tbody tr:hover {
        background: #faf9f5;
    }

    .report-account-name {
        color: var(--text);
        font-weight: 600;
    }

    .report-account-email {
        display: block;
        margin-top: 5px;
        color: var(--muted);
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    .report-status {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        background: #efede4;
        color: var(--muted);
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .report-status.pending {
        background: #fff1cf;
        color: #805b00;
    }

    .report-status.approved,
    .report-status.verified {
        background: #e5f3e5;
        color: #276738;
    }

    .report-status.rejected,
    .report-status.unpaid {
        background: #fbe8e6;
        color: #a33128;
    }

    .report-empty {
        padding: 28px 16px !important;
        color: var(--muted);
        text-align: center !important;
    }

    .report-pagination {
        margin-top: 20px;
    }

    .report-footer {
        margin-top: 28px;
        padding-top: 18px;
        border-top: 1px solid var(--border);
        color: var(--muted);
        font-size: 12px;
        line-height: 1.8;
    }

    .report-footer a {
        color: var(--olive);
        font-weight: 600;
        text-decoration: none;
    }

    .report-footer a:hover {
        color: var(--dark-olive);
        text-decoration: underline;
    }

    @media (max-width: 950px) {
        .report-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 520px) {
        .report-stats {
            grid-template-columns: 1fr;
        }

        .report-stat {
            padding: 18px;
        }

        .report-stat-number {
            font-size: 27px;
        }
    }
</style>
@endpush

@section('content')

    {{-- Report statistics --}}
    <div class="report-stats">

        <div class="report-stat">
            <div class="report-stat-label">Total Applications</div>
            <div class="report-stat-number">
                {{ $totalApplications ?? 0 }}
            </div>
        </div>

        <div class="report-stat">
            <div class="report-stat-label">Pending Applications</div>
            <div class="report-stat-number">
                {{ $pendingApplications ?? 0 }}
            </div>
        </div>

        <div class="report-stat">
            <div class="report-stat-label">Approved Applications</div>
            <div class="report-stat-number">
                {{ $approvedApplications ?? 0 }}
            </div>
        </div>

        <div class="report-stat">
            <div class="report-stat-label">Rejected Applications</div>
            <div class="report-stat-number">
                {{ $rejectedApplications ?? 0 }}
            </div>
        </div>

        <div class="report-stat">
            <div class="report-stat-label">Payments Awaiting Verification</div>
            <div class="report-stat-number">
                {{ $paymentsToVerify ?? 0 }}
            </div>
        </div>

        <div class="report-stat">
            <div class="report-stat-label">Verified Payments</div>
            <div class="report-stat-number">
                {{ $totalPayments ?? 0 }}
            </div>
        </div>

    </div>

    {{-- Recent membership records --}}
    <h2 class="report-section-title">Recent Membership Records</h2>

    <p class="report-muted">
        Latest applications with their currently recorded membership and payment statuses.
    </p>

    <div class="report-table-wrap">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Application</th>
                    <th>Artist</th>
                    <th>Account</th>
                    <th>Membership Status</th>
                    <th>Payment Status</th>
                    <th>Submitted</th>
                </tr>
            </thead>

            <tbody>
                @forelse($applications as $application)
                    <tr>
                        <td>#{{ $application->id }}</td>

                        <td>{{ $application->artist_name }}</td>

                        <td>
                            <span class="report-account-name">
                                {{ $application->user->name ?? 'Unknown user' }}
                            </span>

                            <span class="report-account-email">
                                {{ $application->user->email ?? '' }}
                            </span>
                        </td>

                        <td>
                            @php($membershipStatus = $application->status ?? 'pending')

                            <span class="report-status {{ in_array($membershipStatus, ['pending', 'approved', 'rejected']) ? $membershipStatus : 'pending' }}">
                                {{ ucfirst($membershipStatus) }}
                            </span>
                        </td>

                        <td>
                            @php($paymentStatus = $application->payment_status ?? 'unpaid')

                            <span class="report-status {{ in_array($paymentStatus, ['pending', 'verified', 'unpaid']) ? $paymentStatus : 'unpaid' }}">
                                {{ ucfirst($paymentStatus) }}
                            </span>
                        </td>

                        <td>
                            {{ $application->created_at?->format('d M Y, h:i A') ?? 'Unavailable' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="report-empty">
                            No membership records are available yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($applications->hasPages())
        <div class="report-pagination">
            {{ $applications->links() }}
        </div>
    @endif

    <div class="report-footer">
        Artfy Administration ·
        <a href="{{ route('admin.dashboard') }}">Return to Dashboard</a>
    </div>

@endsection
