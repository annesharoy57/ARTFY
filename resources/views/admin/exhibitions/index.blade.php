@extends('admin.layout')

@section('title', 'Exhibitions | ARTFY Admin')
@section('page_eyebrow', 'Artfy / Exhibition Management')
@section('page_title', 'Exhibition studio')
@section('page_intro', 'Create and manage exhibitions, track artist registrations, and review award settlements.')

@section('content')
    @if (session('success'))
        <div class="exhibition-alert is-success" role="status"><span>✓</span>{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="exhibition-alert is-error" role="alert"><span>!</span>{{ session('error') }}</div>
    @endif

    <section class="exhibition-hero">
        <div class="hero-orbit orbit-a"></div><div class="hero-orbit orbit-b"></div>
        <div class="hero-copy"><span class="hero-kicker"><i></i> CURATE WHAT COMES NEXT</span><h2>Build a place<br>for <em>art to meet.</em></h2><p>Shape the program, welcome artists, and follow every exhibition from registration through awards.</p></div>
        <div class="hero-action"><span class="hero-count">{{ $exhibitions->total() }}<small> exhibitions</small></span><a href="{{ route('admin.exhibitions.create') }}"><span aria-hidden="true">＋</span> Create exhibition</a></div>
        <span class="hero-star" aria-hidden="true">✳</span>
    </section>

    <section class="finance-overview" aria-label="Exhibition financial overview">
        <article class="finance-card"><span class="finance-icon">৳</span><div><span class="finance-label">VERIFIED REGISTRATION FEES</span><strong>BDT {{ number_format((float) $totalExhibitionIncome, 2) }}</strong><small>Confirmed SSLCOMMERZ payments</small></div></article>
        <article class="finance-card commission-card"><span class="finance-icon">✦</span><div><span class="finance-label">ARTFY COMMISSION EARNED</span><strong>BDT {{ number_format((float) $totalArtfyExhibitionCommission, 2) }}</strong><small>20% from finalized exhibitions</small></div></article>
        <a class="finance-explainer" href="{{ route('admin.sales.index') }}"><span class="explainer-icon">↗</span><span><strong>Settlement details</strong><small>View awards and credited amounts in each exhibition.</small></span></a>
    </section>

    <div class="exhibition-list-heading"><div><span class="section-kicker">YOUR PROGRAM</span><h2>All exhibitions</h2></div><span class="list-count">{{ $exhibitions->total() }} TOTAL</span></div>

    <section class="exhibition-list" aria-label="Manage exhibitions">
        @forelse ($exhibitions as $exhibition)
            @php
                $isLive = $exhibition->status === 'published' && $exhibition->visitIsOpen();
                $hasStarted = $exhibition->visitHasStarted();
                $displayStatus = $exhibition->status !== 'published'
                    ? ucfirst($exhibition->status)
                    : ($isLive ? 'Live now' : ($hasStarted ? 'Ended' : 'Upcoming'));
                $expectedCommission = (float) ($exhibition->income_total ?? 0) * .20;
            @endphp
            <article class="exhibition-card">
                <div class="exhibition-cover">
                    @if ($exhibition->cover_image)
                        <img src="{{ asset('storage/' . $exhibition->cover_image) }}" alt="{{ $exhibition->title }} cover" loading="lazy">
                    @else
                        <div class="cover-placeholder" aria-hidden="true"><span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($exhibition->title, 0, 1)) }}</span><i>✳</i></div>
                    @endif
                    <span @class(['exhibition-status', 'status-live' => $isLive, 'status-upcoming' => $displayStatus === 'Upcoming', 'status-ended' => $displayStatus === 'Ended', 'status-draft' => ! in_array($displayStatus, ['Live now', 'Upcoming', 'Ended'], true)])><i></i>{{ $displayStatus }}</span>
                    <span class="cover-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="exhibition-info">
                    <div class="exhibition-title-row"><div>@if ($exhibition->theme || $exhibition->subtitle)<p class="exhibition-theme">{{ $exhibition->theme ?: $exhibition->subtitle }}</p>@else<p class="exhibition-theme">ONLINE EXHIBITION</p>@endif<h3>{{ $exhibition->title }}</h3></div><span class="capacity-badge">{{ $exhibition->enrolled_artists_count }} <small>/ {{ $exhibition->max_artists }}</small></span></div>
                    <p class="exhibition-dates"><span aria-hidden="true">▦</span>{{ $exhibition->starts_at->format('M j, Y') }} <b>—</b> {{ $exhibition->ends_at->format('M j, Y') }}</p>
                    <p class="exhibition-description">{{ \Illuminate\Support\Str::limit(strip_tags($exhibition->description ?? 'An Artfy online exhibition.'), 135) }}</p>

                    <div class="exhibition-metrics">
                        <div><span>ARTISTS ENROLLED</span><strong>{{ $exhibition->enrolled_artists_count }} <small>of {{ $exhibition->max_artists }}</small></strong>@if ($exhibition->pending_payment_count > 0)<em>{{ $exhibition->pending_payment_count }} payment(s) processing</em>@endif</div>
                        <div><span>REGISTRATION FEE</span><strong>BDT {{ number_format((float) $exhibition->registration_fee, 2) }}</strong></div>
                        <div><span>VERIFIED FEES</span><strong>BDT {{ number_format((float) ($exhibition->income_total ?? 0), 2) }}</strong></div>
                    </div>

                    <div class="settlement-strip">
                        @if ($exhibition->settlement)
                            <span class="settlement-status is-finalized"><i></i> Awards finalized</span>
                            <span>Artfy commission <strong>BDT {{ number_format((float) $exhibition->settlement->admin_fund_amount, 2) }}</strong></span>
                            <span>Artist prizes <strong>BDT {{ number_format((float) ($exhibition->prize_credits_total ?? 0), 2) }}</strong></span>
                            @if ((float) $exhibition->settlement->unallocated_prize_amount > 0)<span>Unallocated <strong>BDT {{ number_format((float) $exhibition->settlement->unallocated_prize_amount, 2) }}</strong></span>@endif
                        @else
                            <span class="settlement-status"><i></i> Awards not finalized</span>
                            <span>Expected 20% commission <strong>BDT {{ number_format($expectedCommission, 2) }}</strong></span>
                        @endif
                    </div>

                    <div class="exhibition-actions">
                        <a class="action-primary" href="{{ route('admin.exhibitions.applications', $exhibition) }}">Applications, awards <span aria-hidden="true">→</span></a>
                        <a href="{{ route('admin.exhibitions.edit', $exhibition) }}">Edit exhibition</a>
                        @if ($exhibition->status === 'published')<a href="{{ route('exhibitions.show', $exhibition->slug) }}" target="_blank" rel="noopener">Public page ↗</a>@endif
                    </div>
                </div>
            </article>
        @empty
            <div class="exhibition-empty"><span aria-hidden="true">✳</span><span class="section-kicker">A FRESH CANVAS</span><h3>Your exhibition program starts here</h3><p>Create your first exhibition to set the dates, invite artists, and begin accepting entries.</p><a href="{{ route('admin.exhibitions.create') }}">Create your first exhibition <span aria-hidden="true">→</span></a></div>
        @endforelse
    </section>

    @if ($exhibitions->hasPages())<div class="exhibition-pagination">{{ $exhibitions->links() }}</div>@endif

    <style>
        .exhibition-alert{display:flex;align-items:center;gap:10px;margin:0 0 14px;padding:12px 15px;border-radius:11px;font-size:12px}.exhibition-alert>span{display:grid;place-items:center;width:21px;height:21px;border-radius:50%;background:rgba(255,255,255,.7);font-weight:800}.exhibition-alert.is-success{border:1px solid #c9e5cb;background:var(--success);color:#276738}.exhibition-alert.is-error{border:1px solid #f0cbc7;background:var(--danger);color:#a33128}
        .exhibition-hero{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:30px;min-height:255px;margin:24px 0 20px;padding:36px 43px;border-radius:24px;background:linear-gradient(112deg,#414c2c,#56643e 68%,#6a7650);color:#fffdf5;box-shadow:0 21px 44px rgba(52,61,37,.14)}.hero-copy{position:relative;z-index:1;max-width:600px}.hero-kicker{display:flex;align-items:center;gap:9px;color:#e2d4ad;font-size:9px;font-weight:800;letter-spacing:1.8px}.hero-kicker i{width:20px;height:1px;background:#dfc184}.hero-copy h2{margin:13px 0 10px;color:#fffdf5;font:normal clamp(34px,4.2vw,50px)/1.02 Georgia,serif;letter-spacing:-1px}.hero-copy h2 em{color:#e2c183;font-weight:normal}.hero-copy p{max-width:525px;margin:0;color:#e1e3d6;font-size:12px;line-height:1.75}.hero-action{position:relative;z-index:1;display:grid;justify-items:start;gap:15px;min-width:205px;padding:20px;border:1px solid rgba(255,255,255,.22);border-radius:16px;background:rgba(255,255,255,.09);backdrop-filter:blur(9px)}.hero-count{color:#fffdf5;font:36px/1 Georgia,serif}.hero-count small{color:#e1dfd0;font:11px Arial,sans-serif}.hero-action>a{display:inline-flex;align-items:center;gap:9px;padding:11px 14px;border-radius:9px;background:#dfc184;color:#34402b;text-decoration:none;font-size:11px;font-weight:800;transition:transform .18s,background .18s}.hero-action>a:hover{transform:translateY(-2px);background:#ead39d}.hero-action>a span{font-size:17px}.hero-orbit{position:absolute;z-index:-1;border:1px solid rgba(255,255,255,.11);border-radius:50%;pointer-events:none}.orbit-a{width:380px;height:380px;right:19%;top:-245px}.orbit-b{width:275px;height:275px;right:22%;top:-190px}.hero-star{position:absolute;right:33%;bottom:32px;color:#e1c184;opacity:.7;font-size:19px}
        .finance-overview{display:grid;grid-template-columns:repeat(2,minmax(0,1fr)) minmax(190px,.85fr);gap:12px;margin-bottom:32px}.finance-card{display:flex;align-items:center;gap:13px;min-width:0;padding:16px;border:1px solid var(--border);border-radius:15px;background:rgba(255,255,255,.84);box-shadow:0 8px 22px rgba(56,56,42,.035)}.finance-icon{display:grid;place-items:center;width:41px;height:41px;flex:0 0 41px;border-radius:13px;background:#eff0e5;color:#5a6944;font-size:18px}.commission-card .finance-icon{background:#f2eee2;color:#9c8150}.finance-card>div{display:grid;gap:5px;min-width:0}.finance-label{color:#8b8c79;font-size:8px;font-weight:800;letter-spacing:1px}.finance-card strong{overflow:hidden;color:#35412d;font:600 19px Georgia,serif;text-overflow:ellipsis;white-space:nowrap}.finance-card small{color:#898a7d;font-size:9px}.finance-explainer{display:flex;align-items:center;gap:10px;padding:14px;border:1px solid #e8e4d8;border-radius:15px;background:#f3f2e9;color:#5c6946;text-decoration:none;transition:background .18s}.finance-explainer:hover{background:#eceede}.explainer-icon{display:grid;place-items:center;width:31px;height:31px;flex:0 0 31px;border-radius:10px;background:#fffefa;font-size:15px}.finance-explainer>span:last-child{display:grid;gap:4px}.finance-explainer strong{font-size:10px}.finance-explainer small{color:#858675;font-size:9px;line-height:1.45}
        .exhibition-list-heading{display:flex;align-items:end;justify-content:space-between;gap:15px;margin:0 2px 15px}.section-kicker{display:block;color:#898a76;font-size:9px;font-weight:800;letter-spacing:1.8px}.exhibition-list-heading h2{margin:5px 0 0;color:#303727;font:normal 27px Georgia,serif}.list-count{padding:7px 10px;border:1px solid #e7e2d4;border-radius:30px;background:#fffdf7;color:#656b52;font-size:9px;font-weight:800;letter-spacing:.6px}
        .exhibition-list{display:grid;gap:17px}.exhibition-card{display:grid;grid-template-columns:minmax(180px,230px) minmax(0,1fr);overflow:hidden;border:1px solid var(--border);border-radius:18px;background:#fffefa;box-shadow:0 11px 28px rgba(56,56,42,.045);transition:transform .22s,box-shadow .22s}.exhibition-card:hover{transform:translateY(-2px);box-shadow:0 17px 36px rgba(56,56,42,.09)}.exhibition-cover{position:relative;min-height:100%;overflow:hidden;background:linear-gradient(145deg,#dfe1d2,#c5ccb4)}.exhibition-cover img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform .6s}.exhibition-card:hover .exhibition-cover img{transform:scale(1.04)}.cover-placeholder{position:absolute;inset:0;display:grid;place-items:center;overflow:hidden;background:radial-gradient(circle at 72% 20%,rgba(255,255,255,.46),transparent 30%),linear-gradient(145deg,#c4c9ad,#879471)}.cover-placeholder:before,.cover-placeholder:after{position:absolute;border:1px solid rgba(255,255,255,.35);border-radius:50%;content:""}.cover-placeholder:before{width:260px;height:260px;right:-100px;top:-160px}.cover-placeholder:after{width:180px;height:180px;left:-80px;bottom:-115px}.cover-placeholder span{color:rgba(255,255,255,.8);font:italic 105px Georgia,serif;text-shadow:0 10px 18px rgba(55,60,44,.14)}.cover-placeholder i{position:absolute;right:20%;bottom:18%;color:#fffdf5;font-size:19px;font-style:normal}.exhibition-status{position:absolute;left:13px;top:13px;display:inline-flex;align-items:center;gap:6px;padding:7px 9px;border:1px solid rgba(255,255,255,.58);border-radius:30px;background:rgba(255,254,249,.9);color:#596344;font-size:8px;font-weight:800;letter-spacing:.6px;backdrop-filter:blur(7px)}.exhibition-status i{width:6px;height:6px;border-radius:50%;background:#8f9277}.status-live i{background:#5c8a50;box-shadow:0 0 0 3px rgba(92,138,80,.15)}.status-ended i{background:#a36f5e}.status-draft i{background:#b08b4f}.cover-index{position:absolute;right:14px;bottom:12px;color:white;font:italic 18px Georgia,serif;text-shadow:0 2px 7px rgba(0,0,0,.25)}
        .exhibition-info{min-width:0;padding:20px 22px 16px}.exhibition-title-row{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.exhibition-theme{margin:0 0 5px;color:#888b72;font-size:8px;font-weight:800;letter-spacing:1.3px;text-transform:uppercase}.exhibition-title-row h3{margin:0;color:#313a2a;font:normal 25px/1.12 Georgia,serif;letter-spacing:-.3px}.capacity-badge{display:grid;place-items:center;min-width:47px;height:42px;padding:0 8px;border:1px solid #e7e4d8;border-radius:12px;background:#f7f6ef;color:#4e5d39;font-size:13px;font-weight:800}.capacity-badge small{color:#939282;font-size:9px;font-weight:600}.exhibition-dates{display:flex;align-items:center;gap:8px;margin:10px 0 0;color:#687052;font-size:10px;font-weight:700}.exhibition-dates>span{color:#9c8756}.exhibition-dates b{color:#a5a38f}.exhibition-description{margin:10px 0 13px;color:#818275;font-size:10px;line-height:1.6}.exhibition-metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;padding:11px 0;border-top:1px solid #efede5;border-bottom:1px solid #efede5}.exhibition-metrics>div{display:grid;align-content:start;gap:5px;min-width:0}.exhibition-metrics span{color:#989786;font-size:7px;font-weight:800;letter-spacing:.9px}.exhibition-metrics strong{overflow:hidden;color:#424b37;font-size:10px;text-overflow:ellipsis;white-space:nowrap}.exhibition-metrics strong small{color:#979685;font-size:9px;font-weight:500}.exhibition-metrics em{color:#a17b3b;font-size:8px;font-style:normal}.settlement-strip{display:flex;flex-wrap:wrap;align-items:center;gap:8px 13px;margin-top:10px;color:#878879;font-size:8px}.settlement-strip>span:not(.settlement-status){display:inline-flex;gap:4px}.settlement-strip strong{color:#596544;font-size:9px}.settlement-status{display:inline-flex;align-items:center;gap:5px;padding:5px 7px;border-radius:20px;background:#f1f0e8;color:#777969;font-size:8px;font-weight:800}.settlement-status i{width:5px;height:5px;border-radius:50%;background:#999a84}.settlement-status.is-finalized{background:#edf4e9;color:#557849}.settlement-status.is-finalized i{background:#6c965a}.exhibition-actions{display:flex;align-items:center;flex-wrap:wrap;gap:7px;margin-top:13px}.exhibition-actions a{display:inline-flex;align-items:center;gap:8px;padding:8px 10px;border:1px solid #e6e3d7;border-radius:8px;background:#fffefa;color:#657050;text-decoration:none;font-size:9px;font-weight:700;transition:background .16s,border-color .16s}.exhibition-actions a:hover{border-color:#c6cbb5;background:#f6f7f0}.exhibition-actions a.action-primary{border-color:#566541;background:#566541;color:#fffdf5}.exhibition-actions a.action-primary:hover{border-color:#414c2c;background:#414c2c}.action-primary span{font-size:14px}
        .exhibition-empty{display:grid;justify-items:center;padding:43px 22px;border:1px dashed #d9d5c7;border-radius:18px;background:rgba(255,255,255,.58);text-align:center}.exhibition-empty>span:first-child{display:grid;place-items:center;width:50px;height:50px;margin-bottom:14px;border-radius:50%;background:#ecebdd;color:#75805a;font-size:23px}.exhibition-empty h3{margin:8px 0;color:#39422d;font:normal 24px Georgia,serif}.exhibition-empty p{max-width:430px;margin:0;color:#7e7e72;font-size:11px;line-height:1.65}.exhibition-empty a{display:inline-flex;align-items:center;gap:8px;margin-top:17px;padding:10px 13px;border-radius:9px;background:#566541;color:#fffdf5;text-decoration:none;font-size:10px;font-weight:700}.exhibition-pagination{margin-top:18px;overflow-x:auto}
        @media(max-width:950px){.finance-overview{grid-template-columns:1fr 1fr}.finance-explainer{grid-column:1/-1}.exhibition-card{grid-template-columns:180px minmax(0,1fr)}.exhibition-info{padding:18px}.hero-action{min-width:175px}}
        @media(max-width:680px){.exhibition-hero{align-items:flex-start;flex-direction:column;min-height:0;padding:28px 23px;border-radius:20px}.hero-action{width:100%;min-width:0;grid-template-columns:1fr auto;align-items:center}.hero-action>a{grid-column:2;grid-row:1}.hero-orbit.orbit-a{right:-12%}.finance-overview{grid-template-columns:1fr 1fr;gap:8px}.finance-card{align-items:flex-start;flex-direction:column;padding:12px}.finance-card strong{font-size:16px}.finance-explainer{grid-column:1/-1}.exhibition-card{grid-template-columns:1fr}.exhibition-cover{min-height:195px;height:195px}.exhibition-info{padding:17px}.exhibition-title-row h3{font-size:23px}.exhibition-metrics{gap:5px}.exhibition-metrics span{font-size:6.5px}.exhibition-metrics strong{font-size:9px}.settlement-strip{font-size:8px}.exhibition-list-heading h2{font-size:24px}}
        @media(max-width:420px){.finance-overview{grid-template-columns:1fr}.finance-card{align-items:center;flex-direction:row}.finance-card strong{font-size:18px}.exhibition-actions a{flex:1;justify-content:center}.exhibition-actions a.action-primary{flex:1.4}}
        @media(prefers-reduced-motion:reduce){.exhibition-hero,.exhibition-card,.exhibition-cover img,.finance-explainer,.hero-action>a,.exhibition-actions a{transition:none}}
    </style>
@endsection
