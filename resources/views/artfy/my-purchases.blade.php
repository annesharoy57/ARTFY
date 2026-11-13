@extends('artfy.layout')

@section('title', 'My Purchases | Artfy')

@section('content')
@php
    $resolvePurchaseImage = function ($image) {
        if (blank($image)) {
            return null;
        }

        $image = trim((string) $image);

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        return asset(str_starts_with($image, 'storage/')
            ? $image
            : 'storage/' . ltrim($image, '/'));
    };

    $verifiedSpend = $orders
        ->where('payment_status', 'verified')
        ->sum('total_amount');
@endphp

<main class="purchase-history">
    <section class="purchase-hero">
        <div class="purchase-hero-copy">
            <a class="purchase-back" href="{{ route('artworks.index') }}">&larr; Continue exploring</a>
            <span class="purchase-eyebrow">YOUR ART COLLECTION</span>
            <h1>Every piece<br>has a <em>story.</em></h1>
            <p>Your purchase history, all together. Find your digital downloads and check the progress of your orders here.</p>
        </div>

        <div class="purchase-summary">
            <div class="purchase-summary-item">
                <span class="purchase-summary-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 7.5h16v13H4zM3 4h18v3.5H3zM9 11h6"/><path d="M12 11v9"/></svg>
                </span>
                <span><strong>{{ $orders->count() }}</strong><small>{{ $orders->count() === 1 ? 'order placed' : 'orders placed' }}</small></span>
            </div>
            <div class="purchase-summary-divider"></div>
            <div class="purchase-summary-item">
                <span class="purchase-summary-icon purchase-summary-icon-gold" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 3v18M17 7.5c0-1.7-2.2-3-5-3S7 5.8 7 7.5 9.2 10.5 12 10.5s5 1.3 5 3-2.2 3-5 3-5-1.3-5-3"/></svg>
                </span>
                <span><strong>BDT {{ number_format((float) $verifiedSpend, 2) }}</strong><small>confirmed purchases</small></span>
            </div>
            <div class="purchase-summary-art" aria-hidden="true"><span></span><i></i></div>
        </div>
    </section>

    <section class="purchase-list-section" aria-labelledby="purchase-list-title">
        <div class="purchase-list-heading">
            <div>
                <span class="purchase-eyebrow">YOUR ORDER ARCHIVE</span>
                <h2 id="purchase-list-title">Purchase history</h2>
            </div>
            <span class="purchase-list-count">{{ $orders->count() }} {{ $orders->count() === 1 ? 'record' : 'records' }}</span>
        </div>

        @forelse ($orders as $order)
            @php
                $artwork = $order->artwork;
                $application = $order->exhibitionApplication;
                $artworkTitle = $order->artwork_title ?: ($artwork->title ?? $application?->artwork_title ?? 'Artwork');
                $imagePath = $artwork?->image ?: $application?->artwork_image;
                $imageUrl = $resolvePurchaseImage($imagePath);
                $isDigitalOrder = $artwork?->artwork_type === 'digital'
                    || $application?->artwork_type === 'digital';
                $canDownload = $order->payment_status === 'verified'
                    && $isDigitalOrder
                    && filled($artwork?->digital_file_path ?: $application?->digital_file_path);
                $paymentClass = preg_replace('/[^a-z0-9_-]/', '-', strtolower((string) ($order->payment_status ?: 'pending')));
                $orderClass = preg_replace('/[^a-z0-9_-]/', '-', strtolower((string) ($order->order_status ?: 'pending')));
            @endphp

            <article class="purchase-order-card">
                <div class="purchase-order-art">
                    @if ($imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $artworkTitle }}" loading="lazy" onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
                        <span class="purchase-art-fallback" hidden>Artwork preview unavailable</span>
                    @else
                        <span class="purchase-art-fallback">{{ $application ? 'Exhibition artwork' : 'Artfy collection' }}</span>
                    @endif
                    <span class="purchase-art-number">{{ $application ? 'EXHIBITION' : 'ARTWORK' }}</span>
                </div>

                <div class="purchase-order-content">
                    <div class="purchase-order-topline">
                        <span class="purchase-order-number">ORDER {{ $order->order_number ?: '#' . $order->id }}</span>
                        <span class="purchase-payment-status status-{{ $paymentClass }}"><i></i>{{ ucfirst($order->payment_status ?? 'pending') }}</span>
                    </div>

                    <div class="purchase-product-heading">
                        <div>
                            <h3>{{ $artworkTitle }}</h3>
                            @if ($application?->exhibition)
                                <p class="purchase-product-subtitle">{{ $application->exhibition->title }} exhibition</p>
                            @elseif ($artwork?->artist)
                                <p class="purchase-product-subtitle">By {{ $artwork->artist->artistProfile?->artist_name ?: $artwork->artist->name }}</p>
                            @endif
                        </div>
                        <span class="purchase-total">BDT {{ number_format((float) $order->total_amount, 2) }}</span>
                    </div>

                    <div class="purchase-order-meta">
                        <span><small>Placed</small>{{ $order->created_at?->format('d M Y') ?? 'Date unavailable' }}</span>
                        <span><small>Order status</small><b class="order-status status-{{ $orderClass }}">{{ ucfirst(str_replace('_', ' ', $order->order_status ?? 'pending')) }}</b></span>
                        @if ($isDigitalOrder)
                            <span><small>Fulfillment</small><b>Digital PDF</b></span>
                        @elseif ($order->fulfillment_status)
                            <span><small>Delivery</small><b>{{ ucfirst(str_replace('_', ' ', $order->fulfillment_status)) }}</b></span>
                        @endif
                    </div>

                    <div class="purchase-order-bottom">
                        @if ($canDownload)
                            <a class="purchase-download" href="{{ route('orders.digital-download', $order) }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3"/></svg>
                                Download digital artwork
                            </a>
                        @elseif ($order->payment_status !== 'verified')
                            <span class="purchase-help-note">Your download will be available after payment is confirmed.</span>
                        @elseif ($isDigitalOrder)
                            <span class="purchase-help-note">The digital file is not currently available. Please contact the administrator.</span>
                        @elseif ($application)
                            <span class="purchase-help-note">Exhibition artwork purchase · delivery and artist payout follow the standard order process.</span>
                        @else
                            <span class="purchase-help-note">This order is not a digital artwork download.</span>
                        @endif

                        @if ($order->payment_status === 'verified' && $order->verified_at)
                            <span class="purchase-verified-date">Confirmed {{ $order->verified_at->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="purchase-empty">
                <div class="purchase-empty-mark" aria-hidden="true"><span>✦</span><i></i></div>
                <span class="purchase-eyebrow">YOUR NEXT DISCOVERY AWAITS</span>
                <h3>Your collection starts with one.</h3>
                <p>When you purchase an artwork, its order details and any available downloads will be saved here.</p>
                <a href="{{ route('artworks.index') }}" class="purchase-empty-button">Explore the gallery <span aria-hidden="true">&rarr;</span></a>
            </div>
        @endforelse
    </section>
</main>

<style>
    .purchase-history { --purchase-olive:#4d5b37; --purchase-deep:#35432b; --purchase-muted:#777d67; --purchase-line:#e4e2d6; max-width:1240px; margin:0 auto; padding:42px 26px 82px; color:var(--purchase-deep); }
    .purchase-history *, .purchase-history *::before, .purchase-history *::after { box-sizing:border-box; }
    .purchase-hero { position:relative; display:grid; grid-template-columns:minmax(0,1fr) minmax(330px,.82fr); align-items:center; gap:25px; min-height:345px; overflow:hidden; padding:clamp(28px,5vw,55px); border:1px solid #dedccb; border-radius:26px; background:radial-gradient(ellipse at 82% 6%,rgba(201,181,130,.26),transparent 35%),linear-gradient(125deg,#f7f5e9,#e9ebdd 70%,#f2eee1); box-shadow:0 22px 55px rgba(63,73,45,.08); }
    .purchase-hero-copy { position:relative; z-index:1; max-width:540px; }
    .purchase-back { display:inline-flex; margin-bottom:28px; color:#697653; font-size:12px; font-weight:700; text-decoration:none; }
    .purchase-back:hover { text-decoration:underline; text-underline-offset:4px; }
    .purchase-eyebrow { display:block; color:#8d906f; font-size:10px; font-weight:800; letter-spacing:.18em; }
    .purchase-hero h1 { margin:12px 0 14px; color:#38442d; font-family:'Playfair Display',Georgia,serif; font-size:clamp(42px,5.7vw,70px); font-weight:500; letter-spacing:-.04em; line-height:1; }
    .purchase-hero h1 em { color:#a68b58; font-weight:400; }
    .purchase-hero-copy>p { max-width:440px; margin:0; color:#707660; font-size:13px; line-height:1.8; }
    .purchase-summary { position:relative; display:flex; flex-direction:column; gap:17px; overflow:hidden; padding:24px; border:1px solid rgba(87,101,65,.17); border-radius:21px; background:rgba(255,254,248,.7); box-shadow:0 14px 30px rgba(65,76,44,.07); backdrop-filter:blur(9px); }
    .purchase-summary-item { position:relative; z-index:1; display:flex; align-items:center; gap:14px; min-width:0; }
    .purchase-summary-icon { display:grid; width:45px; height:45px; flex:0 0 45px; place-items:center; border-radius:14px; background:#e7eadc; color:#4d5b37; }
    .purchase-summary-icon-gold { background:#f1ead9; color:#9a8152; }
    .purchase-summary-icon svg { width:21px; height:21px; fill:none; stroke:currentColor; stroke-width:1.6; stroke-linecap:round; stroke-linejoin:round; }
    .purchase-summary-item strong,.purchase-summary-item small { display:block; }
    .purchase-summary-item strong { color:#414c2e; font-family:Georgia,serif; font-size:22px; font-weight:500; line-height:1.2; overflow-wrap:anywhere; }
    .purchase-summary-item small { margin-top:3px; color:#7b806d; font-size:10px; letter-spacing:.03em; }
    .purchase-summary-divider { height:1px; background:linear-gradient(90deg,#dedfce,transparent); }
    .purchase-summary-art { position:absolute; right:-23px; bottom:-58px; width:150px; height:150px; border:1px solid rgba(112,124,82,.16); border-radius:50%; }
    .purchase-summary-art i { position:absolute; inset:17px; border:1px solid rgba(112,124,82,.16); border-radius:50%; }

    .purchase-list-section { padding-top:42px; }
    .purchase-list-heading { display:flex; align-items:end; justify-content:space-between; gap:14px; margin-bottom:20px; }
    .purchase-list-heading h2 { margin:7px 0 0; color:#414c2f; font-family:'Playfair Display',Georgia,serif; font-size:30px; font-weight:500; }
    .purchase-list-count { padding:8px 12px; border:1px solid #e1e2d5; border-radius:99px; background:#f4f4eb; color:#697452; font-size:11px; font-weight:700; }
    .purchase-order-card { display:grid; grid-template-columns:210px minmax(0,1fr); overflow:hidden; margin-bottom:16px; border:1px solid #e3e1d4; border-radius:20px; background:#fffefa; box-shadow:0 10px 28px rgba(61,70,46,.055); transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease; }
    .purchase-order-card:hover { transform:translateY(-3px); border-color:#cbd0bb; box-shadow:0 17px 34px rgba(61,70,46,.1); }
    .purchase-order-art { position:relative; display:grid; min-height:225px; place-items:center; overflow:hidden; padding:13px; background:radial-gradient(ellipse at 50% 35%,#faf7ea,#e7e9dc); }
    .purchase-order-art::after { position:absolute; inset:12px; border:1px solid rgba(95,106,73,.18); content:''; pointer-events:none; }
    .purchase-order-art img { width:100%; height:100%; min-height:199px; object-fit:cover; }
    .purchase-art-fallback { display:grid; min-height:190px; place-items:center; color:#858b72; font-family:Georgia,serif; font-size:13px; text-align:center; }
    .purchase-art-fallback[hidden] { display:none; }
    .purchase-art-number { position:absolute; z-index:1; left:22px; bottom:21px; padding:6px 9px; border:1px solid rgba(255,255,255,.5); border-radius:99px; background:rgba(255,253,247,.86); color:#52603d; font-size:8px; font-weight:800; letter-spacing:.12em; }
    .purchase-order-content { display:flex; min-width:0; flex-direction:column; padding:20px 23px 16px; }
    .purchase-order-topline { display:flex; align-items:center; justify-content:space-between; gap:10px; padding-bottom:13px; border-bottom:1px solid #efede4; }
    .purchase-order-number { color:#8a8e78; font-size:9px; font-weight:800; letter-spacing:.13em; overflow-wrap:anywhere; }
    .purchase-payment-status { display:inline-flex; align-items:center; gap:7px; padding:6px 9px; border:1px solid transparent; border-radius:99px; font-size:9px; font-weight:800; white-space:nowrap; }
    .purchase-payment-status i { width:6px; height:6px; border-radius:50%; background:currentColor; }
    .status-verified { border-color:#d3e2ca; background:#edf4e9; color:#557544; }
    .status-pending,.status-processing { border-color:#efe3bf; background:#fbf5e5; color:#967638; }
    .status-failed,.status-rejected,.status-cancelled { border-color:#efd4cf; background:#fcf0ed; color:#a55b4c; }
    .purchase-product-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:15px; padding:15px 0; }
    .purchase-product-heading h3 { margin:0; color:#35422d; font-family:'Playfair Display',Georgia,serif; font-size:23px; font-weight:500; line-height:1.25; }
    .purchase-product-subtitle { margin:5px 0 0; color:#777d67; font-size:11px; }
    .purchase-total { flex:0 0 auto; color:#485638; font-size:13px; font-weight:800; white-space:nowrap; }
    .purchase-order-meta { display:flex; flex-wrap:wrap; gap:18px 36px; padding:12px 0 15px; border-top:1px solid #efede4; }
    .purchase-order-meta span { display:flex; flex-direction:column; gap:4px; color:#586047; font-size:11px; font-weight:600; }
    .purchase-order-meta small { color:#929582; font-size:9px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
    .order-status { color:#65704f; font-size:11px; font-weight:700; }
    .purchase-order-bottom { display:flex; align-items:center; justify-content:space-between; gap:12px; min-height:38px; margin-top:auto; padding-top:12px; border-top:1px solid #efede4; }
    .purchase-download { display:inline-flex; align-items:center; gap:8px; padding:9px 13px; border-radius:99px; background:#4d5b37; color:#fff; font-size:10px; font-weight:700; text-decoration:none; transition:background .18s ease,transform .18s ease; }
    .purchase-download:hover { transform:translateY(-1px); background:#35432b; color:#fff; }
    .purchase-download svg { width:15px; height:15px; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
    .purchase-help-note,.purchase-verified-date { color:#7b806d; font-size:10px; line-height:1.6; }
    .purchase-verified-date { flex:0 0 auto; color:#71805b; }

    .purchase-empty { display:flex; min-height:350px; flex-direction:column; align-items:center; justify-content:center; padding:40px 20px; border:1px dashed #d7dac9; border-radius:22px; background:linear-gradient(145deg,#fffefa,#f0f1e7); text-align:center; }
    .purchase-empty-mark { position:relative; display:grid; width:86px; height:86px; place-items:center; margin-bottom:20px; border:1px solid #dfe2d3; border-radius:26px; background:#e9ecdf; color:#798563; }
    .purchase-empty-mark span { z-index:1; font-size:37px; }
    .purchase-empty-mark i { position:absolute; width:62px; height:62px; border:1px solid rgba(85,97,58,.18); border-radius:50%; }
    .purchase-empty h3 { margin:10px 0 8px; color:#414c2f; font-family:'Playfair Display',Georgia,serif; font-size:29px; font-weight:500; }
    .purchase-empty p { max-width:440px; margin:0; color:#757b66; font-size:13px; line-height:1.8; }
    .purchase-empty-button { display:inline-flex; align-items:center; gap:12px; margin-top:21px; padding:12px 17px; border-radius:99px; background:#4d5b37; color:#fff; font-size:12px; font-weight:700; text-decoration:none; }
    .purchase-empty-button:hover { background:#35432b; color:#fff; }

    @media(max-width:760px) { .purchase-history{padding:25px 16px 55px}.purchase-hero{grid-template-columns:1fr;padding:30px 24px}.purchase-summary{display:grid;grid-template-columns:1fr auto 1fr;gap:12px;padding:17px}.purchase-summary-item{gap:9px}.purchase-summary-icon{width:38px;height:38px;flex-basis:38px}.purchase-summary-item strong{font-size:17px}.purchase-order-card{grid-template-columns:150px minmax(0,1fr)}.purchase-order-art{min-height:100%}.purchase-order-art img{min-height:180px}.purchase-order-content{padding:16px}.purchase-order-meta{gap:12px 22px} }
    @media(max-width:540px) { .purchase-hero{padding:25px 20px}.purchase-hero h1{font-size:47px}.purchase-summary{grid-template-columns:1fr;gap:13px}.purchase-summary-divider{height:1px}.purchase-order-card{grid-template-columns:1fr}.purchase-order-art{height:220px;min-height:0}.purchase-order-art img{min-height:194px}.purchase-product-heading{flex-direction:column;gap:8px}.purchase-order-bottom{align-items:flex-start;flex-direction:column}.purchase-verified-date{padding-left:1px}.purchase-list-heading{align-items:flex-start;flex-direction:column} }
    @media(prefers-reduced-motion:reduce) { .purchase-history *,.purchase-history *::before,.purchase-history *::after{scroll-behavior:auto!important;transition:none!important;animation:none!important} }
</style>
@endsection
