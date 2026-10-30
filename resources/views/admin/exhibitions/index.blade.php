@extends('admin.layout')

@section('title', 'Exhibitions | ARTFY Admin')
@section('page_eyebrow', 'Artfy / Exhibition Management')
@section('page_title', 'Online exhibitions')
@section('page_intro', 'Create exhibitions, set artist capacity and fees, and review confirmed registration income.')

@section('content')
    @if (session('success'))
        <p class="exhibition-admin-message success">{{ session('success') }}</p>
    @endif
    @if (session('error'))
        <p class="exhibition-admin-message error">{{ session('error') }}</p>
    @endif
    <a class="exhibition-admin-button" href="{{ route('admin.exhibitions.create') }}">Create exhibition</a>

    <section class="exhibition-income-total">
        <span>Total verified exhibition fees</span>
        <strong>৳{{ number_format((float) $totalExhibitionIncome, 2) }}</strong>
        <small>Calculated from confirmed SSLCOMMERZ payments.</small>
    </section>

    <div class="exhibition-admin-list">
        @forelse ($exhibitions as $exhibition)
            <article class="exhibition-admin-card">
                <div>
                    <span class="exhibition-admin-status">{{ ucfirst($exhibition->status) }}</span>
                    <h2>{{ $exhibition->title }}</h2>
                    <p>{{ $exhibition->starts_at->format('M j, Y') }} – {{ $exhibition->ends_at->format('M j, Y') }}</p>
                    <p>
                        {{ $exhibition->enrolled_artists_count }} / {{ $exhibition->max_artists }} artists enrolled
                        @if ($exhibition->pending_payment_count > 0)
                            · {{ $exhibition->pending_payment_count }} pending payment(s), not counted until verified
                        @endif
                        · Fee ৳{{ number_format((float) $exhibition->registration_fee, 2) }}
                    </p>
                    <p>Verified exhibition income: <strong>৳{{ number_format((float) ($exhibition->income_total ?? 0), 2) }}</strong></p>
                </div>
                <div class="exhibition-admin-actions">
                    <a href="{{ route('admin.exhibitions.edit', $exhibition) }}">Edit</a>
                    <a href="{{ route('admin.exhibitions.applications', $exhibition) }}">Applications &amp; income</a>
                    @if ($exhibition->status === 'published')
                        <a href="{{ route('exhibitions.show', $exhibition->slug) }}" target="_blank" rel="noopener">View exhibition</a>
                    @endif
                </div>
            </article>
        @empty
            <p>No exhibitions yet. Create one to begin accepting artists.</p>
        @endforelse
    </div>
    {{ $exhibitions->links() }}

    <style>
        .exhibition-admin-message{padding:12px 16px;border-radius:8px}.exhibition-admin-message.success{background:#e5f3e5}.exhibition-admin-message.error{background:#fbe8e6}
        .exhibition-admin-button,.exhibition-admin-actions a{display:inline-block;padding:10px 14px;border-radius:7px;background:#414c2c;color:#fff;text-decoration:none}
        .exhibition-income-total{display:grid;gap:7px;max-width:380px;margin-top:20px;padding:20px;border:1px solid var(--border);border-radius:12px;background:#fff}
        .exhibition-income-total span,.exhibition-income-total small{color:var(--muted)}
        .exhibition-income-total strong{font-size:28px}
        .exhibition-admin-list{display:grid;gap:14px;margin-top:22px}
        .exhibition-admin-card{display:flex;justify-content:space-between;gap:22px;padding:20px;border:1px solid var(--border);border-radius:12px;background:#fff}
        .exhibition-admin-card h2{margin:8px 0}.exhibition-admin-card p{color:var(--muted)}
        .exhibition-admin-status{font-size:12px;color:var(--olive);text-transform:uppercase}
        .exhibition-admin-actions{display:flex;align-items:flex-start;flex-wrap:wrap;gap:8px}
        @media(max-width:700px){.exhibition-admin-card{display:block}.exhibition-admin-actions{margin-top:16px}}
    </style>
@endsection
