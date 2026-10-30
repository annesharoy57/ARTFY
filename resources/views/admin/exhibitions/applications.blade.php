@extends('admin.layout')

@section('title', 'Exhibition Applications | ARTFY Admin')
@section('page_eyebrow', 'Artfy / Exhibition Management')
@section('page_title', $exhibition->title)
@section('page_intro', 'Artist reservations, payment confirmation, and income for this exhibition.')

@section('content')
    <p><a href="{{ route('admin.exhibitions.index') }}">← Back to exhibitions</a></p>
    <section class="exhibition-income">
        <div><span>Verified registration income</span><strong>৳{{ number_format((float) $confirmedIncome, 2) }}</strong></div>
        <div><span>Artists enrolled</span><strong>{{ $enrolledCount }} / {{ $exhibition->max_artists }}</strong></div>
        <div><span>Paid slots used</span><strong>{{ $reservedCount }} / {{ $exhibition->max_artists }}</strong></div>
        @if ($pendingPaymentCount > 0)
            <div><span>Pending checkout payments</span><strong>{{ $pendingPaymentCount }}</strong></div>
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
        .exhibition-income div,.exhibition-application{padding:18px;border:1px solid var(--border);border-radius:10px;background:#fff}
        .exhibition-income span,.exhibition-income strong{display:block}.exhibition-income span{color:var(--muted);font-size:13px}.exhibition-income strong{margin-top:6px;font-size:22px}
        .exhibition-application-list{display:grid;gap:12px}.exhibition-application h2{margin:0 0 8px;font-size:18px}.exhibition-application p{margin:5px 0;color:var(--muted)}
        .exhibition-submitted-artwork{max-width:620px;margin-top:16px;padding-top:14px;border-top:1px solid var(--border)}
        .exhibition-submitted-artwork img{display:block;width:min(100%,360px);max-height:300px;object-fit:contain;background:#f5f3e8}
        .exhibition-submitted-artwork h3{margin:12px 0 6px}
    </style>
@endsection
