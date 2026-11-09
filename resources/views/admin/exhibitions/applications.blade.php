@extends('admin.layout')

@section('title', 'Exhibition Applications | ARTFY Admin')
@section('page_eyebrow', 'Artfy / Exhibition Management')
@section('page_title', $exhibition->title)
@section('page_intro', 'Artist reservations, payment confirmation, and income for this exhibition.')

@section('content')
    <p><a href="{{ route('admin.exhibitions.index') }}">← Back to exhibitions</a></p>
    @if (session('success'))
        <p class="exhibition-admin-message success" role="status">{{ session('success') }}</p>
    @endif
    @if (session('error'))
        <p class="exhibition-admin-message error" role="alert">{{ session('error') }}</p>
    @endif
    <section class="exhibition-income">
        <div><span>Verified registration income</span><strong>৳{{ number_format((float) $confirmedIncome, 2) }}</strong></div>
        <div><span>Artists enrolled</span><strong>{{ $enrolledCount }} / {{ $exhibition->max_artists }}</strong></div>
        <div><span>Paid slots used</span><strong>{{ $reservedCount }} / {{ $exhibition->max_artists }}</strong></div>
        @if ($pendingPaymentCount > 0)
            <div><span>Pending checkout payments</span><strong>{{ $pendingPaymentCount }}</strong></div>
        @endif
        <div><span>Artfy commission recorded across exhibitions</span><strong>৳{{ number_format((float) $adminFundTotal, 2) }}</strong></div>
    </section>
    <section class="exhibition-awards">
        <h2>Voting and awards</h2>
        <p>Final points are art-lover votes plus all co-admin scores. Tied artists share the combined prize allocations.</p>
        @if ($settlement)
            <p><strong>Awards finalized:</strong> {{ $settlement->settled_at->format('M j, Y g:i A') }}</p>
            <p>Verified fees: ৳{{ number_format((float) $settlement->total_fees, 2) }} · Artfy commission (20%): ৳{{ number_format((float) $settlement->admin_fund_amount, 2) }} · Unallocated prize amount: ৳{{ number_format((float) $settlement->unallocated_prize_amount, 2) }}</p>
            <p>Artist prize credits issued: ৳{{ number_format((float) $prizeCreditsTotal, 2) }}</p>
        @elseif ($exhibition->status === 'published' && $exhibition->visitHasEnded())
            <form method="POST" action="{{ route('admin.exhibitions.finalize-awards', $exhibition) }}" onsubmit="return confirm('Finalize this exhibition and credit prize money to the winning artists? This cannot be undone.')">
                @csrf
                <button type="submit">Finalize winners and credit prizes</button>
            </form>
            <p>This action credits prize amounts to artist wallets and records Artfy's 20% commission.</p>
        @else
            <p>Winners and commission can be credited after this published exhibition closes.</p>
        @endif
    </section>
    <div class="exhibition-application-list">
        @forelse ($applications as $application)
            <article class="exhibition-application">
                <div>
                    <h2>{{ $application->artist?->artistProfile?->artist_name ?: $application->artist?->name }}</h2>
                    <p>{{ $application->artist?->email }}</p>
                    <p>Application: {{ ucfirst($application->status) }} · Payment: {{ ucfirst($application->payment_status) }}</p>
                    <p>Fee: ৳{{ number_format((float) $application->registration_fee, 2) }} · Reference: {{ $application->payment_reference ?: '—' }}</p>
                    @if ($application->status === 'pending')
                        <p>
                            {{ $application->isActivePaymentReservation()
                                ? 'Checkout is active. This application does not count toward artist capacity until payment is verified.'
                                : 'Checkout expired without payment and does not count toward artist capacity. The artist can retry while registration is open.' }}
                        </p>
                    @endif
                    @if ($application->artwork_title)
                        <div class="exhibition-submitted-artwork">
                            @if ($application->artwork_image)
                                <img src="{{ asset('storage/' . $application->artwork_image) }}" alt="{{ $application->artwork_title }}">
                            @endif
                            <h3>{{ $application->artwork_title }}</h3>
                            <p>{{ $application->artwork_description }}</p>
                            <p>Artist's asking price: ৳{{ number_format((float) $application->artwork_price, 2) }}</p>
                            @if ($exhibition->visitHasEnded())
                                <p>Final points: {{ $application->votes_count }} art-lover vote(s) + {{ (int) ($application->scores_sum_score ?? 0) }} co-admin point(s) = <strong>{{ $application->votes_count + (int) ($application->scores_sum_score ?? 0) }}</strong></p>
                            @endif
                            @if ($settlement && $prizeCredits->has($application->id))
                                <p>Prize credited to artist wallet: <strong>৳{{ number_format((float) $prizeCredits->get($application->id)->amount, 2) }}</strong></p>
                            @endif
                        </div>
                    @elseif ($application->status === 'capacity_review')
                        <p>Payment is verified, but no artist place remained when it completed. This application needs support follow-up.</p>
                    @elseif ($application->payment_status === 'verified')
                        <p>Exhibition fee paid; awaiting the artist's artwork submission.</p>
                    @endif
                    @if ($application->payment_verified_at)
                        <p>Verified {{ $application->payment_verified_at->format('M j, Y g:i A') }}</p>
                    @endif
                </div>
            </article>
        @empty
            <p>No artists have applied yet.</p>
        @endforelse
    </div>
    {{ $applications->links() }}

    <style>
        .exhibition-income{display:flex;flex-wrap:wrap;gap:14px;margin:20px 0}
        .exhibition-admin-message{padding:12px 16px;border-radius:8px}.exhibition-admin-message.success{background:#e5f3e5;color:#285b31}.exhibition-admin-message.error{background:#fbe8e6;color:#922f32}
        .exhibition-awards{margin:18px 0;padding:18px;border:1px solid var(--border);border-radius:10px;background:#fff}.exhibition-awards h2{margin-top:0}
        .exhibition-awards button{padding:11px 16px;border:0;border-radius:6px;background:var(--dark-olive);color:#fff;font:inherit;font-weight:700;cursor:pointer}
        .exhibition-income div,.exhibition-application{padding:18px;border:1px solid var(--border);border-radius:10px;background:#fff}
        .exhibition-income span,.exhibition-income strong{display:block}.exhibition-income span{color:var(--muted);font-size:13px}.exhibition-income strong{margin-top:6px;font-size:22px}
        .exhibition-application-list{display:grid;gap:12px}.exhibition-application h2{margin:0 0 8px;font-size:18px}.exhibition-application p{margin:5px 0;color:var(--muted)}
        .exhibition-submitted-artwork{max-width:620px;margin-top:16px;padding-top:14px;border-top:1px solid var(--border)}
        .exhibition-submitted-artwork img{display:block;width:min(100%,360px);max-height:300px;object-fit:contain;background:#f5f3e8}
        .exhibition-submitted-artwork h3{margin:12px 0 6px}
    </style>
@endsection
