@extends('coadmin.layout')

@section('title', 'Online Exhibitions | Artfy Co-admin')

@section('content')
    @php
        $liveCount = $exhibitions->filter(fn ($item) => $item->visitIsOpen())->count();
        $upcomingCount = $exhibitions->filter(fn ($item) => ! $item->visitHasStarted())->count();
    @endphp

    <section class="workspace-hero">
        <div class="hero-ornament ornament-a"></div><div class="hero-ornament ornament-b"></div>
        <div class="hero-text">
            <span class="hero-kicker"><i></i> JUDGING WORKSPACE</span>
            <h1>Exhibitions<br><em>in the spotlight.</em></h1>
            <p>Explore the show, meet the participating artists, and give each artwork the thoughtful score it deserves.</p>
        </div>
        <div class="hero-totals" aria-label="Exhibition summary">
            <div><span class="total-number">{{ str_pad((string) $liveCount, 2, '0', STR_PAD_LEFT) }}</span><span class="total-label">OPEN NOW</span></div>
            <div class="totals-rule"></div>
            <div><span class="total-number">{{ str_pad((string) $upcomingCount, 2, '0', STR_PAD_LEFT) }}</span><span class="total-label">UPCOMING</span></div>
        </div>
        <span class="hero-flower" aria-hidden="true">✳</span>
    </section>

    <div class="listing-heading">
        <div><span class="listing-kicker">YOUR CURATED VIEW</span><h2>Exhibition schedule</h2></div>
        <span class="listing-total">{{ $exhibitions->count() }} {{ Str::plural('exhibition', $exhibitions->count()) }}</span>
    </div>

    @if ($exhibitions->isNotEmpty())
        <section class="exhibition-grid" aria-label="Exhibitions">
            @foreach ($exhibitions as $exhibition)
                @php
                    $isOpen = $exhibition->visitIsOpen();
                    $hasStarted = $exhibition->visitHasStarted();
                    $accent = preg_replace('/[^#a-fA-F0-9]/', '', $exhibition->accent_color ?: '#697650');
                @endphp
                <article class="exhibition-card" style="--card-accent: {{ $accent }}">
                    <div class="card-art">
                        @if ($exhibition->cover_image)
                            <img src="{{ asset('storage/' . $exhibition->cover_image) }}" alt="{{ $exhibition->title }} exhibition artwork" loading="lazy">
                        @else
                            <div class="art-placeholder" aria-hidden="true"><span>{{ mb_strtoupper(mb_substr($exhibition->title, 0, 1)) }}</span><i>✳</i></div>
                        @endif
                        <div class="art-shade"></div>
                        <span class="status-badge {{ $isOpen ? 'is-live' : ($hasStarted ? 'is-closed' : 'is-upcoming') }}"><i></i>{{ $isOpen ? 'SCORING OPEN' : ($hasStarted ? 'EXHIBITION ENDED' : 'UPCOMING') }}</span>
                        <span class="art-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="card-content">
                        @if ($exhibition->theme || $exhibition->subtitle)
                            <p class="card-theme">{{ $exhibition->theme ?: $exhibition->subtitle }}</p>
                        @else
                            <p class="card-theme">ONLINE EXHIBITION</p>
                        @endif
                        <h3>{{ $exhibition->title }}</h3>
                        <p class="card-description">{{ \Illuminate\Support\Str::limit(strip_tags($exhibition->description ?? 'Explore the participating artists and their work.'), 112) }}</p>
                        <div class="card-meta">
                            <div class="date-icon" aria-hidden="true">▦</div>
                            <div><span class="meta-label">EXHIBITION DATES</span><strong>{{ $exhibition->starts_at->format('M j') }} <span>—</span> {{ $exhibition->ends_at->format('M j, Y') }}</strong></div>
                        </div>
                        <div class="card-footer">
                            <span class="participant-count"><strong>{{ $exhibition->participant_count }}</strong> {{ Str::plural('artwork', $exhibition->participant_count) }}</span>
                            @if ($hasStarted)
                                <a class="review-link" href="{{ route('co-admin.exhibitions.show', $exhibition) }}">{{ $isOpen ? 'Review artwork' : 'View results' }} <span aria-hidden="true">↗</span></a>
                            @else
                                <span class="review-link is-locked">Opens {{ $exhibition->starts_at->format('M j') }} <span aria-hidden="true">◷</span></span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @else
        <section class="empty-state">
            <span class="empty-symbol" aria-hidden="true">✳</span>
            <span class="listing-kicker">THE GALLERY IS QUIET</span>
            <h2>No published exhibitions yet</h2>
            <p>When an exhibition is published, it will appear here with its dates and participant artwork.</p>
        </section>
    @endif

    <style>
        .workspace-hero{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:30px;min-height:300px;margin:8px 0 36px;padding:42px 48px;border-radius:26px;background:linear-gradient(112deg,#414c2c 0%,#52603a 62%,#6b7650 100%);color:#fffdf5;box-shadow:0 22px 48px rgba(52,61,37,.15)}
        .hero-text{position:relative;z-index:1;max-width:600px}.hero-kicker{display:flex;align-items:center;gap:9px;color:#dfd3ac;font-size:10px;font-weight:800;letter-spacing:2px}.hero-kicker i{width:22px;height:1px;background:#dfc184}.hero-text h1{margin:16px 0 12px;font:normal clamp(40px,5vw,61px)/.98 Georgia,serif;letter-spacing:-1.8px}.hero-text h1 em{color:#e2c183;font-weight:normal}.hero-text p{max-width:475px;margin:0;color:#e1e3d7;font-size:14px;line-height:1.8}
        .hero-totals{position:relative;z-index:1;display:flex;align-items:center;gap:25px;padding:22px 25px;border:1px solid rgba(255,255,255,.24);border-radius:18px;background:rgba(255,255,255,.09);backdrop-filter:blur(9px)}.hero-totals>div:not(.totals-rule){display:flex;flex-direction:column;gap:7px}.total-number{font:38px/1 Georgia,serif;color:#fffdf5}.total-label{color:#e0d5b4;font-size:9px;font-weight:800;letter-spacing:1.5px}.totals-rule{height:48px;width:1px;background:rgba(255,255,255,.24)}.hero-flower{position:absolute;z-index:-1;right:34%;top:19px;color:#d9c288;font-size:28px;opacity:.65}.hero-ornament{position:absolute;z-index:-1;border:1px solid rgba(255,255,255,.12);border-radius:50%;pointer-events:none}.ornament-a{width:430px;height:430px;right:19%;top:-285px}.ornament-b{width:320px;height:320px;right:22%;top:-225px}
        .listing-heading{display:flex;align-items:end;justify-content:space-between;gap:16px;margin:0 2px 18px}.listing-kicker{display:block;color:#878a70;font-size:9px;font-weight:800;letter-spacing:1.8px}.listing-heading h2{margin:5px 0 0;color:#303727;font:normal 28px/1.1 Georgia,serif}.listing-total{padding:8px 12px;border:1px solid #e7e2d4;border-radius:30px;background:#fffdf7;color:#656b52;font-size:11px;font-weight:700}
        .exhibition-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:22px}.exhibition-card{overflow:hidden;border:1px solid #e8e4d9;border-radius:20px;background:#fffefa;box-shadow:0 12px 30px rgba(55,56,40,.055);transition:transform .24s ease,box-shadow .24s ease}.exhibition-card:hover{transform:translateY(-5px);box-shadow:0 20px 38px rgba(55,56,40,.12)}.card-art{position:relative;overflow:hidden;height:225px;background:linear-gradient(135deg,color-mix(in srgb,var(--card-accent) 65%,#e3d8bb),#d8dccb)}.card-art img{display:block;width:100%;height:100%;object-fit:cover;transition:transform .65s cubic-bezier(.2,.7,.2,1)}.exhibition-card:hover .card-art img{transform:scale(1.045)}.art-shade{position:absolute;inset:0;background:linear-gradient(180deg,rgba(24,31,21,.22),transparent 46%,rgba(24,31,21,.14));pointer-events:none}.art-placeholder{position:absolute;inset:0;display:grid;place-items:center;overflow:hidden;background:radial-gradient(circle at 73% 24%,rgba(255,255,255,.43),transparent 22%),linear-gradient(135deg,color-mix(in srgb,var(--card-accent) 78%,#d9cda8),color-mix(in srgb,var(--card-accent) 42%,#ebe5d4))}.art-placeholder:before,.art-placeholder:after{position:absolute;content:"";border:1px solid rgba(255,255,255,.35);border-radius:50%}.art-placeholder:before{width:280px;height:280px;right:-65px;top:-185px}.art-placeholder:after{width:190px;height:190px;left:-85px;bottom:-145px}.art-placeholder span{color:rgba(255,255,255,.76);font:italic 130px/1 Georgia,serif;text-shadow:0 12px 25px rgba(50,57,36,.14)}.art-placeholder i{position:absolute;right:23%;bottom:20%;color:rgba(255,255,255,.65);font-size:28px;font-style:normal}.status-badge{position:absolute;top:16px;left:17px;display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border:1px solid rgba(255,255,255,.38);border-radius:30px;background:rgba(255,255,255,.92);color:#4b513c;font-size:9px;font-weight:800;letter-spacing:.8px;box-shadow:0 5px 16px rgba(0,0,0,.08);backdrop-filter:blur(8px)}.status-badge i{width:6px;height:6px;border-radius:50%;background:#9a986d}.status-badge.is-live i{background:#5d8b51;box-shadow:0 0 0 3px rgba(93,139,81,.14)}.status-badge.is-closed i{background:#a8786b}.art-index{position:absolute;right:16px;bottom:14px;color:rgba(255,255,255,.9);font:italic 22px Georgia,serif;text-shadow:0 2px 8px rgba(0,0,0,.25)}
        .card-content{padding:22px 23px 17px}.card-theme{margin:0 0 8px;color:var(--card-accent);font-size:9px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase}.card-content h3{margin:0;color:#303727;font:normal 29px/1.1 Georgia,serif}.card-description{min-height:40px;margin:10px 0 16px;color:#77796c;font-size:12px;line-height:1.7}.card-meta{display:flex;align-items:center;gap:11px;padding:12px 0;border-top:1px solid #efede5;border-bottom:1px solid #efede5}.date-icon{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;background:#eff0e6;color:#596744;font-size:16px}.meta-label{display:block;margin-bottom:4px;color:#989785;font-size:8px;font-weight:800;letter-spacing:1.2px}.card-meta strong{color:#454b38;font-size:12px}.card-meta strong span{padding:0 3px;color:#a5a28f}.card-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:14px}.participant-count{color:#87877b;font-size:11px}.participant-count strong{color:#4a513d;font-size:13px}.review-link{display:inline-flex;align-items:center;gap:9px;color:#53613a;text-decoration:none;font-size:11px;font-weight:800}.review-link span{transition:transform .2s}.review-link:hover{color:#343e26}.review-link:hover span{transform:translate(2px,-2px)}
        .empty-state{display:grid;justify-items:center;padding:52px 22px;border:1px dashed #d9d5c7;border-radius:20px;background:rgba(255,255,255,.55);text-align:center}.empty-symbol{display:grid;place-items:center;width:54px;height:54px;margin-bottom:16px;border-radius:50%;background:#ecebdd;color:#75805a;font-size:25px}.empty-state h2{margin:8px 0;color:#343d2b;font:normal 27px Georgia,serif}.empty-state p{max-width:430px;margin:0;color:#7c7d71;font-size:13px;line-height:1.7}
        @media(max-width:820px){.workspace-hero{align-items:flex-start;flex-direction:column;min-height:0;padding:34px}.hero-totals{width:max-content}.exhibition-grid{grid-template-columns:1fr 1fr}.card-art{height:195px}}
        @media(max-width:580px){.workspace-hero{padding:27px 23px;border-radius:20px}.hero-text h1{font-size:44px}.hero-totals{width:100%;justify-content:center}.exhibition-grid{grid-template-columns:1fr;gap:16px}.card-art{height:220px}.listing-heading h2{font-size:25px}.card-content{padding:20px}.ornament-a{right:-20%;}}
        @media(prefers-reduced-motion:reduce){.exhibition-card,.card-art img,.review-link span{transition:none}}
    </style>
@endsection
