@extends('admin.layout')

@section('title', 'Sales & Commission | ARTFY Admin')
@section('page_eyebrow', 'Financial Overview')
@section('page_title', 'Sales & Commission')
@section('page_intro', 'Review completed artwork sales, recorded Artfy commission, and the artist earnings recorded for each verified order.')

@push('styles')
<style>
    .section-heading {
        margin: 30px 0 16px;
        font-family: Georgia, serif;
        font-size: 23px;
        font-weight: normal;
        color: var(--dark-olive);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 17px;
    }

    .stat-card {
        min-width: 0;
        padding: 23px 20px;
        border: 1px solid var(--border);
        border-radius: 13px;
        background: var(--white);
        box-shadow: 0 4px 18px rgba(43, 48, 32, 0.04);
    }

    .stat-label {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .stat-value {
        overflow-wrap: anywhere;
        margin-top: 13px;
        font-family: Georgia, serif;
        font-size: clamp(19px, 1.8vw, 27px);
        font-weight: normal;
        color: var(--dark-olive);
    }

    .stat-note {
        margin-top: 8px;
        color: var(--muted);
        font-size: 11px;
        line-height: 1.5;
    }

    .table-card {
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 13px;
        background: var(--white);
        box-shadow: 0 4px 18px rgba(43, 48, 32, 0.04);
    }

    .table-intro {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 20px 22px;
        border-bottom: 1px solid var(--border);
    }

    .table-intro h3 {
        margin: 0;
        font-size: 15px;
        color: var(--text);
    }

    .table-intro p {
        margin: 6px 0 0;
        color: var(--muted);
        font-size: 12px;
        line-height: 1.6;
    }

    .verified-label {
        padding: 7px 10px;
        border-radius: 20px;
        background: var(--success);
        color: #276738;
        font-size: 11px;
        font-weight: bold;
        white-space: nowrap;
    }

    .table-scroll {
        overflow-x: auto;
    }

    .sales-table {
        width: 100%;
        min-width: 1050px;
        border-collapse: collapse;
        text-align: left;
    }

    .sales-table th {
        padding: 14px 17px;
        background: #fbfaf7;
        color: var(--muted);
        font-size: 10px;
        font-weight: bold;
        letter-spacing: .7px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .sales-table td {
        padding: 17px;
        border-top: 1px solid var(--border);
        font-size: 12px;
        line-height: 1.6;
        vertical-align: top;
    }

    .sales-table tbody tr:hover {
        background: #fdfcf9;
    }

    .primary-text {
        color: var(--text);
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .secondary-text {
        display: block;
        margin-top: 3px;
        color: var(--muted);
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .money {
        white-space: nowrap;
        font-weight: 600;
    }

    .commission {
        color: var(--dark-olive);
    }

    .sale-status {
        display: inline-block;
        padding: 5px 8px;
        border-radius: 20px;
        background: var(--success);
        color: #276738;
        font-size: 10px;
        font-weight: bold;
    }

    .fulfillment-form { display:grid; gap:7px; min-width:190px; }
    .fulfillment-form select,.fulfillment-form input { width:100%; padding:8px 9px; border:1px solid var(--border); border-radius:8px; background:#fff; color:var(--text); font-size:11px; }
    .fulfillment-form button { padding:8px 10px; border:0; border-radius:8px; background:var(--dark-olive); color:#fff; font-size:11px; font-weight:700; cursor:pointer; }
    .fulfillment-current { display:inline-block; padding:5px 8px; border-radius:20px; background:#f4f0e2; color:#796638; font-size:10px; font-weight:700; }
    .pending-label { display:inline-block; padding:5px 8px; border-radius:20px; background:#fff3d9; color:#95702e; font-size:10px; font-weight:700; }
    .pending-check-form { margin-top:9px; }
    .pending-check-form button { padding:8px 10px; border:1px solid var(--border); border-radius:8px; background:#fff; color:var(--dark-olive); font-size:11px; font-weight:700; cursor:pointer; }
    .pending-check-form button:hover { background:#f5f4eb; }

    .empty-state {
        padding: 45px 20px;
        color: var(--muted);
        text-align: center;
        line-height: 1.7;
    }

    .empty-state h3 {
        margin: 0 0 8px;
        color: var(--dark-olive);
        font-family: Georgia, serif;
        font-size: 21px;
        font-weight: normal;
    }

    .ledger-pagination {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 18px 22px;
        border-top: 1px solid var(--border);
    }

    .pagination-info {
        color: var(--muted);
        font-size: 12px;
    }

    .ledger-pagination nav p,
    .ledger-pagination nav a,
    .ledger-pagination nav span {
        font-size: 12px;
    }

    .footnote {
        margin-top: 18px;
        color: var(--muted);
        font-size: 11px;
        line-height: 1.7;
    }

    .section-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 36px;
        font-size: 26px;
        letter-spacing: -.02em;
    }

    .section-heading::after {
        content: '';
        height: 1px;
        flex: 1;
        background: linear-gradient(90deg, var(--border), transparent);
    }

    .stats-grid { gap: 18px; }

    .stat-card {
        position: relative;
        overflow: hidden;
        padding: 24px;
        border-radius: 18px;
        background: linear-gradient(145deg, #fff 55%, #f1f0e5);
        box-shadow: 0 10px 28px rgba(43, 48, 32, .055);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 36px rgba(43, 48, 32, .1);
    }

    .stat-card:nth-child(2) { background: linear-gradient(145deg, #fff 55%, #f5eee2); }
    .stat-card:nth-child(3) { background: linear-gradient(145deg, #fff 55%, #edf1e6); }
    .stat-card:nth-child(4) { background: linear-gradient(145deg, #fff 55%, #f5f0df); }
    .stat-label { font-size: 12px; font-weight: 700; letter-spacing: .035em; }
    .stat-value { font-size: clamp(20px, 2vw, 29px); letter-spacing: -.025em; }

    .table-card {
        border-radius: 18px;
        box-shadow: 0 12px 32px rgba(43, 48, 32, .06);
    }

    .table-intro { padding: 22px 24px; background: linear-gradient(110deg, #fff, #f7f6ef); }
    .table-intro h3 { font-family: Georgia, serif; font-size: 20px; font-weight: normal; }
    .verified-label { border: 1px solid #cde2cf; padding: 8px 13px; }
    .sales-table th { background: #f3f2e9; }
    .sales-table td { padding-top: 18px; padding-bottom: 18px; }
    .sales-table tbody tr { transition: background .15s ease; }
    .sales-table tbody tr:hover { background: #f7f7f0; }
    .sale-status { border: 1px solid #cde2cf; }

    @media (prefers-reduced-motion: reduce) { .stat-card { transition: none; } }

    @media (max-width: 1150px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .table-intro {
            align-items: flex-start;
            flex-direction: column;
            padding: 17px;
        }

        .ledger-pagination {
            align-items: flex-start;
            flex-direction: column;
            padding: 16px;
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

    <section>
        <h2 class="section-heading">Sales Summary</h2>

        <div class="stats-grid">

            <article class="stat-card">
                <div class="stat-label">Verified Artwork Sales</div>

                <div class="stat-value">
                    {{ number_format($totalSoldArtworks) }}
                </div>

                <div class="stat-note">
                    Orders with verified payment status
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-label">Total Sales Value</div>

                <div class="stat-value">
                    BDT {{ number_format((float) $totalSalesValue, 2) }}
                </div>

                <div class="stat-note">
                    Combined value of verified artwork orders
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-label">Artfy Commission</div>

                <div class="stat-value">
                    BDT {{ number_format((float) $totalCommissionEarned, 2) }}
                </div>

                <div class="stat-note">
                    Commission amounts recorded in the orders
                </div>
            </article>

            <article class="stat-card">
                <div class="stat-label">Artist Earnings</div>

                <div class="stat-value">
                    BDT {{ number_format((float) $totalArtistEarnings, 2) }}
                </div>

                <div class="stat-note">
                    Artist amounts recorded in the orders
                </div>
            </article>

        </div>
    </section>

    <section>
        <h2 class="section-heading">Pending Artwork Payments</h2>

        <div class="table-card">
            <div class="table-intro">
                <div>
                    <h3>Checkouts awaiting confirmation</h3>
                    <p>Includes exhibition artwork orders. These become sales only after SSLCOMMERZ verifies payment.</p>
                </div>
                <span class="pending-label">{{ $pendingSales->total() }} pending</span>
            </div>

            @if ($pendingSales->count() > 0)
                <div class="table-scroll">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Artwork / Exhibition</th>
                                <th>Buyer</th>
                                <th>Artist</th>
                                <th>Started</th>
                                <th>Sale Price</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingSales as $pendingSale)
                                <tr>
                                    <td><span class="primary-text">{{ $pendingSale->order_number }}</span></td>
                                    <td>
                                        <span class="primary-text">{{ $pendingSale->artwork_title ?: 'Untitled artwork' }}</span>
                                        @if ($pendingSale->exhibitionApplication?->exhibition)
                                            <span class="secondary-text">Exhibition: {{ $pendingSale->exhibitionApplication->exhibition->title }}</span>
                                        @else
                                            <span class="secondary-text">Uploaded artwork</span>
                                        @endif
                                    </td>
                                    <td>{{ $pendingSale->buyer?->name ?? 'Unknown buyer' }}</td>
                                    <td>{{ $pendingSale->artist?->name ?? 'Unknown artist' }}</td>
                                    <td>{{ $pendingSale->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</td>
                                    <td class="money">BDT {{ number_format((float) $pendingSale->price, 2) }}</td>
                                    <td>
                                        <span class="pending-label">{{ ucfirst($pendingSale->payment_status) }}</span>
                                        @if ($pendingSale->payment_method === 'sslcommerz')
                                            <form class="pending-check-form" method="POST" action="{{ route('admin.sales.payment-status', $pendingSale) }}">
                                                @csrf
                                                <button type="submit">Check payment status</button>
                                            </form>
                                        @else
                                            <span class="secondary-text">{{ ucfirst($pendingSale->payment_method) }} - Manual review</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="ledger-pagination">
                    <div class="pagination-info">
                        Showing {{ $pendingSales->firstItem() ?? 0 }} to {{ $pendingSales->lastItem() ?? 0 }} of {{ $pendingSales->total() }} pending orders
                    </div>
                    <div>{{ $pendingSales->links() }}</div>
                </div>
            @else
                <div class="empty-state">
                    <h3>No pending artwork payments</h3>
                    <p>New exhibition and uploaded artwork checkouts will appear here until payment is verified.</p>
                </div>
            @endif
        </div>
    </section>

    <section>
        <h2 class="section-heading">Verified Sales Ledger</h2>

        <div class="table-card">

            <div class="table-intro">
                <div>
                    <h3>Artwork Transaction History</h3>
                    <p>
                        Verified orders only, newest verification first.
                    </p>
                </div>

                <span class="verified-label">Verified Orders</span>
            </div>

            @if ($sales->count() > 0)

                <div class="table-scroll">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Artwork</th>
                                <th>Buyer</th>
                                <th>Artist</th>
                                <th>Verified Date</th>
                                <th>Sale Price</th>
                                <th>Commission</th>
                                <th>Artist Share</th>
                                <th>Delivery progress</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($sales as $sale)
                                @php
                                    $saleArtworkType = $sale->exhibitionApplication?->artwork_type ?? $sale->artwork?->artwork_type ?? 'physical';
                                    $fulfillmentStatus = $sale->fulfillment_status ?: 'pending';
                                @endphp
                                <tr>
                                    <td>
                                        <span class="primary-text">
                                            {{ $sale->order_number ?: 'N/A' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="primary-text">
                                            {{ $sale->artwork_title ?: 'Untitled artwork' }}
                                        </span>

                                        <span class="secondary-text">
                                            Order #{{ $sale->id }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="primary-text">
                                            {{ $sale->buyer?->name ?? 'Unknown buyer' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="primary-text">
                                            {{ $sale->artist?->name ?? 'Unknown artist' }}
                                        </span>
                                    </td>

                                    <td>
                                        @if ($sale->verified_at)
                                            {{ $sale->verified_at->format('d M Y') }}

                                            <span class="secondary-text">
                                                {{ $sale->verified_at->format('h:i A') }}
                                            </span>
                                        @else
                                            {{ $sale->created_at?->format('d M Y') ?? 'N/A' }}

                                            <span class="secondary-text">
                                                Created date (verification date unavailable)
                                            </span>
                                        @endif
                                    </td>

                                    <td class="money">
                                        BDT {{ number_format((float) $sale->price, 2) }}
                                    </td>

                                    <td>
                                        <span class="money commission">
                                            BDT {{ number_format((float) $sale->commission_amount, 2) }}
                                        </span>

                                        <span class="secondary-text">
                                            {{ number_format((float) $sale->commission_rate, 2) }}%
                                        </span>
                                    </td>

                                    <td class="money">
                                        BDT {{ number_format((float) $sale->artist_amount, 2) }}
                                    </td>

                                    <td>
                                        <span class="fulfillment-current">{{ ucfirst(str_replace('_', ' ', $fulfillmentStatus)) }}</span>
                                        @if ($saleArtworkType === 'physical' && $fulfillmentStatus !== 'delivered')
                                            <form class="fulfillment-form" method="POST" action="{{ route('admin.sales.fulfillment', $sale) }}">
                                                @csrf
                                                @method('PATCH')
                                                <select name="fulfillment_status" aria-label="Update delivery status">
                                                    <option value="processing" @selected($fulfillmentStatus === 'processing')>Preparing order</option>
                                                    <option value="shipped" @selected($fulfillmentStatus === 'shipped')>Shipped</option>
                                                    <option value="delivered">Delivered</option>
                                                </select>
                                                <input name="tracking_number" value="{{ $sale->tracking_number }}" placeholder="Tracking number (optional)" aria-label="Tracking number">
                                                <button type="submit">Save delivery update</button>
                                            </form>
                                        @elseif ($saleArtworkType === 'digital')
                                            <span class="secondary-text">Digital artwork</span>
                                        @elseif ($sale->tracking_number)
                                            <span class="secondary-text">Tracking: {{ $sale->tracking_number }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="ledger-pagination">
                    <div class="pagination-info">
                        Showing {{ $sales->firstItem() ?? 0 }}
                        to {{ $sales->lastItem() ?? 0 }}
                        of {{ $sales->total() }} verified orders
                    </div>

                    <div>
                        {{ $sales->links() }}
                    </div>
                </div>

            @else

                <div class="empty-state">
                    <h3>No verified sales yet</h3>

                    <p>
                        Completed artwork orders will appear here after
                        their payments have been verified.
                    </p>
                </div>

            @endif
        </div>

        <p class="footnote">
            Financial note: These totals represent amounts recorded in the
            database for verified orders. They do not independently confirm
            that commission or artist earnings have been transferred.
            Pending and failed orders are excluded.
        </p>
    </section>

@endsection
