@extends('admin.layout')

@section('title', 'Membership Payments | Artfy Admin')
@section('page_eyebrow', 'Artfy / Finance')
@section('page_title', 'Membership payment ledger')
@section('page_intro', 'Track SSLCOMMERZ payment confirmations. Successful payments are verified automatically; review and approve the artist application separately.')

@section('content')
    <section class="ledger-hero">
        <div class="ledger-hero-copy">
            <span class="ledger-kicker">PAYMENT OPERATIONS</span>
            <h2>Every payment,<br><em>clearly accounted for.</em></h2>
            <p>SSLCOMMERZ confirms payment directly with Artfy. Use this ledger to follow payment status, then review the artist’s application in Memberships.</p>
            <a href="{{ route('admin.memberships.index') }}" class="review-applications-link">Review artist applications <span aria-hidden="true">→</span></a>
        </div>
        <div class="ledger-hero-mark" aria-hidden="true"><span>৳</span><i>✳</i></div>
    </section>

    <section class="ledger-stats" aria-label="Payment summary">
        <article class="ledger-stat stat-received"><span class="stat-icon">৳</span><div><span class="stat-label">PAYMENTS RECEIVED</span><strong>BDT {{ number_format($paymentSummary['received'], 2) }}</strong><small>{{ $paymentSummary['verified'] }} verified payment(s)</small></div></article>
        <article class="ledger-stat"><span class="stat-icon">✓</span><div><span class="stat-label">VERIFIED</span><strong>{{ $paymentSummary['verified'] }}</strong><small>Confirmed by SSLCOMMERZ</small></div></article>
        <article class="ledger-stat"><span class="stat-icon pending-icon">◷</span><div><span class="stat-label">IN PROGRESS</span><strong>{{ $paymentSummary['pending'] }}</strong><small>Waiting for gateway confirmation</small></div></article>
        <article class="ledger-stat"><span class="stat-icon review-icon">✦</span><div><span class="stat-label">NEEDS APPLICATION REVIEW</span><strong>{{ $paymentSummary['membershipReview'] }}</strong><small>Paid artists awaiting admin decision</small></div></article>
    </section>

    @if (session('success'))
        <div class="ledger-alert is-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="ledger-alert is-error" role="alert">{{ session('error') }}</div>
    @endif

    <section class="ledger-panel">
        <div class="ledger-panel-heading">
            <div><span class="ledger-kicker">RECENT ACTIVITY</span><h2>Membership payments</h2></div>
            <span class="record-count">{{ $payments->total() }} records</span>
        </div>

        <nav class="ledger-filters" aria-label="Filter payments">
            @foreach (['all' => 'All payments', 'verified' => 'Verified', 'pending' => 'In progress', 'unpaid' => 'Unpaid'] as $status => $label)
                <a href="{{ route('admin.payments.index', ['status' => $status]) }}" @class(['is-active' => $filter === $status]) aria-current="{{ $filter === $status ? 'page' : 'false' }}">{{ $label }}
                    @if ($status === 'all')<small>{{ $paymentSummary['total'] }}</small>@elseif (isset($paymentSummary[$status]))<small>{{ $paymentSummary[$status] }}</small>@endif
                </a>
            @endforeach
        </nav>

        @if ($payments->isNotEmpty())
            <div class="ledger-table-wrap">
                <table class="ledger-table">
                    <thead><tr><th>ARTIST</th><th>ARTFY REFERENCE</th><th>METHOD</th><th>AMOUNT</th><th>PAYMENT STATUS</th><th>MEMBERSHIP</th><th>LAST UPDATE</th></tr></thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            @php
                                $artistName = $payment->artist_name ?: $payment->user?->name ?: 'Unknown artist';
                                $paymentStatus = $payment->payment_status ?: 'unpaid';
                                $membershipStatus = $payment->status ?: 'pending';
                                $updatedAt = $payment->payment_verified_at ?: $payment->payment_submitted_at ?: $payment->updated_at;
                            @endphp
                            <tr>
                                <td><div class="artist-cell"><span class="artist-initial">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($artistName, 0, 1)) }}</span><span><strong>{{ $artistName }}</strong><small>{{ $payment->user?->email ?: 'No account email' }}</small></span></div></td>
                                <td><code>{{ $payment->payment_reference ?: 'Not assigned' }}</code></td>
                                <td>{{ $payment->payment_method === 'sslcommerz' ? 'SSLCOMMERZ' : ($payment->payment_method ? ucfirst($payment->payment_method) : 'Not started') }}</td>
                                <td class="amount-cell">BDT {{ number_format((float) ($payment->membership_fee ?? 0), 2) }}</td>
                                <td><span @class(['status-pill', 'status-verified' => $paymentStatus === 'verified', 'status-pending' => $paymentStatus === 'pending', 'status-unpaid' => $paymentStatus === 'unpaid', 'status-other' => ! in_array($paymentStatus, ['verified', 'pending', 'unpaid'], true)])><i></i>{{ ucfirst(str_replace('_', ' ', $paymentStatus)) }}</span></td>
                                <td><span @class(['membership-pill', 'membership-approved' => $membershipStatus === 'approved', 'membership-pending' => $membershipStatus === 'pending', 'membership-rejected' => $membershipStatus === 'rejected'])>{{ ucfirst($membershipStatus) }}</span></td>
                                <td class="date-cell">{{ $updatedAt?->format('M j, Y') ?? '—' }}<small>{{ $updatedAt?->format('g:i A') ?? '' }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="ledger-mobile-list">
                @foreach ($payments as $payment)
                    @php
                        $artistName = $payment->artist_name ?: $payment->user?->name ?: 'Unknown artist';
                        $paymentStatus = $payment->payment_status ?: 'unpaid';
                        $membershipStatus = $payment->status ?: 'pending';
                        $updatedAt = $payment->payment_verified_at ?: $payment->payment_submitted_at ?: $payment->updated_at;
                    @endphp
                    <article class="mobile-payment-card">
                        <div class="mobile-artist"><span class="artist-initial">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($artistName, 0, 1)) }}</span><div><strong>{{ $artistName }}</strong><small>{{ $payment->user?->email ?: 'No account email' }}</small></div></div>
                        <div class="mobile-payment-meta"><span>AMOUNT<strong>BDT {{ number_format((float) ($payment->membership_fee ?? 0), 2) }}</strong></span><span>PAYMENT METHOD<strong>{{ $payment->payment_method === 'sslcommerz' ? 'SSLCOMMERZ' : ($payment->payment_method ? ucfirst($payment->payment_method) : 'Not started') }}</strong></span><span>ARTFY REFERENCE<code>{{ $payment->payment_reference ?: 'Not assigned' }}</code></span></div>
                        <div class="mobile-status-row"><span @class(['status-pill', 'status-verified' => $paymentStatus === 'verified', 'status-pending' => $paymentStatus === 'pending', 'status-unpaid' => $paymentStatus === 'unpaid', 'status-other' => ! in_array($paymentStatus, ['verified', 'pending', 'unpaid'], true)])><i></i>{{ ucfirst(str_replace('_', ' ', $paymentStatus)) }}</span><span @class(['membership-pill', 'membership-approved' => $membershipStatus === 'approved', 'membership-pending' => $membershipStatus === 'pending', 'membership-rejected' => $membershipStatus === 'rejected'])>{{ ucfirst($membershipStatus) }}</span></div>
                        <small class="mobile-updated">Updated {{ $updatedAt?->format('M j, Y g:i A') ?? '—' }}</small>
                    </article>
                @endforeach
            </div>
            @if ($payments->hasPages())<div class="ledger-pagination">{{ $payments->links() }}</div>@endif
        @else
            <div class="ledger-empty"><span aria-hidden="true">✳</span><h3>No payment records in this view</h3><p>Try another status filter, or check back when an artist starts a membership payment.</p></div>
        @endif
    </section>

    <style>
        .ledger-hero{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:30px;min-height:235px;margin:26px 0 21px;padding:32px 40px;border-radius:22px;background:linear-gradient(112deg,#414c2c,#5a6742 70%,#707b55);color:#fffdf5;box-shadow:0 20px 42px rgba(52,61,37,.13)}.ledger-hero-copy{position:relative;z-index:1;max-width:670px}.ledger-kicker{display:block;color:#8a8c75;font-size:9px;font-weight:800;letter-spacing:1.8px}.ledger-hero .ledger-kicker{color:#e0d2ac}.ledger-hero h2{margin:10px 0;color:#fffdf5;font:normal clamp(31px,4vw,43px)/1.05 Georgia,serif;letter-spacing:-.8px}.ledger-hero h2 em{color:#e2c183;font-weight:normal}.ledger-hero p{max-width:610px;margin:0;color:#e3e4d9;font-size:12px;line-height:1.7}.review-applications-link{display:inline-flex;align-items:center;gap:10px;margin-top:16px;padding:10px 13px;border:1px solid rgba(255,255,255,.32);border-radius:9px;color:#fffdf5;text-decoration:none;font-size:11px;font-weight:700;transition:background .18s}.review-applications-link:hover{background:rgba(255,255,255,.12)}.review-applications-link span{font-size:16px}.ledger-hero-mark{position:relative;display:grid;place-items:center;width:155px;height:155px;flex:0 0 155px;border:1px solid rgba(255,255,255,.2);border-radius:50%;background:rgba(255,255,255,.08);box-shadow:0 0 0 17px rgba(255,255,255,.04),inset 0 0 35px rgba(255,255,255,.05)}.ledger-hero-mark span{display:grid;place-items:center;width:95px;height:95px;border:1px solid rgba(232,207,150,.72);border-radius:50%;color:#e2c183;font:42px Georgia,serif}.ledger-hero-mark i{position:absolute;top:12px;right:9px;color:#e2c183;font-size:18px;font-style:normal}
        .ledger-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:24px}.ledger-stat{display:flex;align-items:center;gap:11px;min-width:0;padding:15px 14px;border:1px solid var(--border);border-radius:14px;background:rgba(255,255,255,.8);box-shadow:0 8px 22px rgba(56,56,42,.035)}.stat-icon{display:grid;place-items:center;width:36px;height:36px;flex:0 0 36px;border-radius:11px;background:#eef0e5;color:#596b43;font-size:17px}.pending-icon{color:#9a7441;background:#f7f0df}.review-icon{color:#7c7155;background:#f2eee2}.ledger-stat>div{display:grid;gap:4px;min-width:0}.stat-label{color:#888977;font-size:8px;font-weight:800;letter-spacing:1px}.ledger-stat strong{overflow:hidden;color:#343d2b;font:600 18px Georgia,serif;text-overflow:ellipsis;white-space:nowrap}.ledger-stat small{overflow:hidden;color:#8b8c7d;font-size:9px;text-overflow:ellipsis;white-space:nowrap}
        .ledger-panel{overflow:hidden;border:1px solid var(--border);border-radius:18px;background:rgba(255,255,255,.88);box-shadow:0 12px 32px rgba(56,56,42,.045)}.ledger-panel-heading{display:flex;align-items:end;justify-content:space-between;gap:12px;padding:22px 24px 14px}.ledger-panel-heading h2{margin:5px 0 0;color:#303727;font:normal 25px Georgia,serif}.record-count{padding:7px 10px;border:1px solid #e7e2d4;border-radius:30px;background:#faf8f0;color:#656b52;font-size:10px;font-weight:700}.ledger-filters{display:flex;gap:6px;overflow:auto;padding:0 24px 16px;border-bottom:1px solid #efede5}.ledger-filters a{display:inline-flex;align-items:center;gap:7px;flex:0 0 auto;padding:8px 10px;border:1px solid transparent;border-radius:9px;color:#76796a;text-decoration:none;font-size:10px;font-weight:700}.ledger-filters a:hover,.ledger-filters a.is-active{border-color:#e5e4d8;background:#f1f2e9;color:#4c5b39}.ledger-filters small{display:grid;place-items:center;min-width:18px;height:18px;padding:0 4px;border-radius:20px;background:#e4e7d9;color:#596746;font-size:9px}
        .ledger-table-wrap{overflow-x:auto}.ledger-table{width:100%;border-collapse:collapse;text-align:left}.ledger-table th{padding:13px 14px;background:#faf9f4;color:#939282;font-size:8px;font-weight:800;letter-spacing:1.1px;white-space:nowrap}.ledger-table th:first-child,.ledger-table td:first-child{padding-left:24px}.ledger-table td{padding:14px;border-top:1px solid #f0eee7;color:#565b4a;font-size:10px;vertical-align:middle}.ledger-table tr:hover td{background:#fdfcf8}.artist-cell{display:flex;align-items:center;gap:10px;min-width:185px}.artist-initial{display:grid;place-items:center;width:36px;height:36px;flex:0 0 36px;border:1px solid #e2dcc7;border-radius:50%;background:linear-gradient(145deg,#e4d3ac,#cbd1b5);color:#52603e;font:16px Georgia,serif}.artist-cell>span:last-child,.mobile-artist>div{display:grid;gap:4px}.artist-cell strong,.mobile-artist strong{color:#414a35;font-size:11px}.artist-cell small,.mobile-artist small{color:#8b8c7d;font-size:9px}.ledger-table code,.mobile-payment-meta code{display:inline-block;padding:5px 7px;border:1px solid #eeece3;border-radius:6px;background:#faf9f4;color:#667050;font:9px Consolas,monospace;overflow-wrap:anywhere}.amount-cell{color:#414c2c!important;font-weight:700;white-space:nowrap}.status-pill,.membership-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 8px;border-radius:30px;font-size:9px;font-weight:800;white-space:nowrap}.status-pill i{width:6px;height:6px;border-radius:50%;background:currentColor}.status-verified{background:#edf4e9;color:#4f7443}.status-pending{background:#fff3d9;color:#95702e}.status-unpaid{background:#f0efe9;color:#77786e}.status-other{background:#fcece8;color:#a35446}.membership-pill{background:#f0efe9;color:#77786e}.membership-pending{background:#fff3d9;color:#95702e}.membership-approved{background:#edf4e9;color:#4f7443}.membership-rejected{background:#fcece8;color:#a35446}.date-cell{white-space:nowrap}.date-cell small{display:block;margin-top:3px;color:#99998c;font-size:9px}.ledger-pagination{padding:15px 20px;border-top:1px solid #efede5;overflow-x:auto}.ledger-mobile-list{display:none}.ledger-empty{display:grid;justify-items:center;padding:43px 20px;text-align:center}.ledger-empty>span{display:grid;place-items:center;width:48px;height:48px;border-radius:50%;background:#ecebdd;color:#75805a;font-size:22px}.ledger-empty h3{margin:12px 0 5px;color:#39422d;font:normal 22px Georgia,serif}.ledger-empty p{max-width:430px;margin:0;color:#7e7e72;font-size:11px;line-height:1.6}.ledger-alert{margin:0 0 15px;padding:12px 15px;border-radius:10px;font-size:12px}.ledger-alert.is-success{background:var(--success);color:#276738}.ledger-alert.is-error{background:var(--danger);color:#a33128}
        @media(max-width:1000px){.ledger-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.ledger-hero-mark{width:125px;height:125px;flex-basis:125px}.ledger-hero-mark span{width:78px;height:78px}}
        @media(max-width:680px){.ledger-hero{align-items:flex-start;min-height:0;padding:27px 22px}.ledger-hero-mark{position:absolute;right:18px;top:18px;width:67px;height:67px;flex-basis:67px;opacity:.68}.ledger-hero-mark span{width:48px;height:48px;font-size:24px}.ledger-hero-mark i{top:0;right:0;font-size:12px}.ledger-hero-copy{padding-right:25px}.ledger-hero h2{font-size:32px}.ledger-stats{gap:8px}.ledger-stat{align-items:flex-start;flex-direction:column;padding:12px}.ledger-stat strong{font-size:16px}.ledger-panel-heading{padding:19px 17px 12px}.ledger-filters{padding:0 17px 13px}.ledger-table-wrap{display:none}.ledger-mobile-list{display:grid;gap:10px;padding:13px}.mobile-payment-card{padding:14px;border:1px solid #ebe7dc;border-radius:13px;background:#fffefa}.mobile-artist{display:flex;align-items:center;gap:9px}.mobile-payment-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:13px 0;padding:11px 0;border-top:1px solid #efede5;border-bottom:1px solid #efede5}.mobile-payment-meta>span{display:grid;gap:5px;color:#929282;font-size:8px;font-weight:800;letter-spacing:.8px}.mobile-payment-meta strong{color:#414c2c;font-size:10px;letter-spacing:0}.mobile-status-row{display:flex;align-items:center;justify-content:space-between;gap:8px}.mobile-updated{display:block;margin-top:9px;color:#929282;font-size:9px}.ledger-pagination{padding:12px}}
        @media(max-width:400px){.ledger-stats{grid-template-columns:1fr 1fr}.ledger-stat{min-height:108px}.ledger-hero-copy{padding-right:0}.ledger-hero-mark{opacity:.25}}
    </style>

@endsection
