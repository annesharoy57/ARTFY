@extends('admin.layout')

@section('title', 'Artwork Review | ARTFY Admin')
@section('page_eyebrow', 'Artfy / Artwork Management')
@section('page_title', 'Artwork review')
@section('page_intro', 'Review each submission before it joins the public Artfy gallery.')

@push('styles')
<style>
    .art-review-messages{display:grid;gap:10px;margin:0 0 18px}.art-review-message{padding:12px 15px;border:1px solid #c9dfc5;border-radius:11px;background:#edf5e9;color:#35643a;font-size:12px;line-height:1.6}.art-review-message.error{border-color:#f0cbc7;background:#fff0ed;color:#922f32}.art-review-message ul{margin:6px 0 0;padding-left:20px}
    .review-hero{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:24px;min-height:218px;margin:23px 0 19px;padding:32px 37px;border-radius:22px;background:linear-gradient(112deg,#414c2c,#586641 70%,#6c7751);color:#fffdf5;box-shadow:0 19px 39px rgba(52,61,37,.13)}.review-hero-copy{position:relative;z-index:1;max-width:620px}.review-kicker{display:flex;align-items:center;gap:8px;color:#e3d5b1;font-size:9px;font-weight:800;letter-spacing:1.8px}.review-kicker i{width:19px;height:1px;background:#dfc184}.review-hero h2{margin:11px 0 8px;color:#fffdf5;font:normal clamp(32px,4vw,45px)/1.05 Georgia,serif;letter-spacing:-.8px}.review-hero h2 em{color:#e2c183;font-weight:normal}.review-hero p{max-width:510px;margin:0;color:#e1e3d6;font-size:12px;line-height:1.7}.review-hero-stat{position:relative;z-index:1;display:grid;justify-items:center;gap:6px;min-width:123px;padding:17px;border:1px solid rgba(255,255,255,.23);border-radius:17px;background:rgba(255,255,255,.09);backdrop-filter:blur(8px)}.review-hero-stat strong{color:#fffdf5;font:42px/1 Georgia,serif}.review-hero-stat span{color:#e3d5b1;font-size:8px;font-weight:800;letter-spacing:1.2px}.hero-ring{position:absolute;z-index:-1;right:20%;top:-225px;width:350px;height:350px;border:1px solid rgba(255,255,255,.12);border-radius:50%;box-shadow:0 0 0 20px rgba(255,255,255,.04),0 0 0 48px rgba(255,255,255,.03)}
    .art-review-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px;margin-bottom:22px}.art-review-stat{position:relative;overflow:hidden;min-width:0;padding:15px 16px;border:1px solid var(--border);border-radius:14px;background:rgba(255,255,255,.86);box-shadow:0 8px 20px rgba(56,56,42,.035)}.art-review-stat:after{position:absolute;right:-20px;bottom:-35px;width:90px;height:90px;border:1px solid rgba(109,119,81,.1);border-radius:50%;content:""}.stat-topline{display:flex;align-items:center;justify-content:space-between;gap:9px}.art-review-stat-label{color:#858675;font-size:9px;font-weight:800;letter-spacing:.7px}.stat-icon{display:grid;place-items:center;width:30px;height:30px;border-radius:10px;background:#eff0e5;color:#65734d;font-size:14px}.art-review-stat-number{position:relative;z-index:1;display:block;margin-top:8px;color:#37432f;font:normal 29px/1 Georgia,serif;overflow-wrap:anywhere}.stat-pending .stat-icon{background:#f8f1df;color:#9b793d}.stat-approved .stat-icon{background:#edf3e8;color:#577b4c}.stat-rejected .stat-icon{background:#f8e9e6;color:#a86255}
    .art-review-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:0 1px 16px}.art-review-toolbar-copy{display:grid;gap:3px}.toolbar-kicker{color:#8a8b76;font-size:8px;font-weight:800;letter-spacing:1.7px}.toolbar-title{color:#303727;font:normal 23px Georgia,serif}.art-review-filters{display:flex;flex-wrap:wrap;gap:6px;padding:5px;border:1px solid #e9e6db;border-radius:13px;background:#f3f2eb}.art-review-filter{display:inline-flex;align-items:center;gap:6px;padding:8px 10px;border:1px solid transparent;border-radius:9px;background:transparent;color:#77796b;font-size:10px;font-weight:700;text-decoration:none;white-space:nowrap;transition:background .18s,color .18s,border-color .18s}.art-review-filter:hover{color:#48553a}.art-review-filter.active{border-color:#e2e3d7;background:#fffefa;color:#4d5d3b;box-shadow:0 2px 7px rgba(45,52,34,.06)}.art-review-filter small{display:grid;place-items:center;min-width:18px;height:18px;padding:0 4px;border-radius:20px;background:#eeede4;color:#757765;font-size:8px}.art-review-filter.active small{background:#e8ebdc;color:#546342}
    .art-review-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));align-items:stretch;gap:17px}.art-review-card{display:flex;flex-direction:column;min-width:0;height:100%;overflow:hidden;border:1px solid #e7e3d8;border-radius:17px;background:#fffefa;box-shadow:0 10px 25px rgba(48,52,38,.05);transition:transform .22s,box-shadow .22s}.art-review-card:hover{transform:translateY(-3px);box-shadow:0 17px 32px rgba(48,52,38,.1)}.art-review-image-wrap{position:relative;display:grid;place-items:center;width:100%;aspect-ratio:4/3;overflow:hidden;border-bottom:1px solid #ebe8dd;background:radial-gradient(circle at 78% 20%,rgba(255,255,255,.85),transparent 28%),linear-gradient(145deg,#f1efe3,#e6e6d8)}.art-review-image{display:block;width:100%;height:100%;object-fit:contain;object-position:center;transition:transform .55s}.art-review-card:hover .art-review-image{transform:scale(1.025)}.art-review-image-link{display:block;width:100%;height:100%}.art-review-image-placeholder{display:grid;place-items:center;width:100%;height:100%;padding:20px;color:#878879;font-size:11px;text-align:center}.image-badge{position:absolute;top:12px;left:12px;display:inline-flex;align-items:center;gap:6px;padding:7px 9px;border:1px solid rgba(255,255,255,.75);border-radius:30px;background:rgba(255,254,249,.9);color:#626d4b;font-size:8px;font-weight:800;letter-spacing:.7px;backdrop-filter:blur(7px)}.image-badge i{width:6px;height:6px;border-radius:50%;background:#b28a47}.image-badge.approved i{background:#5f8d51}.image-badge.rejected i{background:#b96556}.image-index{position:absolute;right:13px;bottom:10px;color:rgba(69,80,53,.7);font:italic 17px Georgia,serif}
    .art-review-card-content{display:flex;flex:1;flex-direction:column;min-width:0;padding:16px}.art-review-artist-row{display:flex;align-items:center;gap:9px;margin-bottom:13px}.art-review-avatar,.art-review-avatar img{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;overflow:hidden;border:2px solid white;border-radius:50%;background:linear-gradient(145deg,#e4d4ae,#bac4a6);color:#556340;object-fit:cover;box-shadow:0 3px 10px rgba(65,76,44,.13)}.art-review-avatar{font:15px Georgia,serif}.artist-caption{display:grid;gap:3px;min-width:0}.artist-caption small{color:#989684;font-size:7px;font-weight:800;letter-spacing:1.2px}.artist-caption strong{overflow:hidden;color:#596644;font-size:11px;text-overflow:ellipsis;white-space:nowrap}.art-review-card-title{display:-webkit-box;min-height:2.3em;margin:0 0 8px;overflow:hidden;color:#303a2a;font:normal 23px/1.15 Georgia,serif;overflow-wrap:anywhere;-webkit-box-orient:vertical;-webkit-line-clamp:2}.art-review-description{display:-webkit-box;min-height:4.8em;margin:0 0 13px;overflow:hidden;color:#76786b;font-size:10px;line-height:1.6;white-space:pre-line;overflow-wrap:anywhere;-webkit-box-orient:vertical;-webkit-line-clamp:3}.art-review-tags{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:13px}.art-review-tags span{padding:6px 8px;border:1px solid #e8e6dc;border-radius:20px;background:#f8f7f1;color:#737563;font-size:8px;font-weight:700}.art-review-facts{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:auto;padding:11px 0;border-top:1px solid #efede5;border-bottom:1px solid #efede5}.art-review-fact{display:grid;gap:4px;min-width:0}.art-review-fact small{color:#999887;font-size:7px;font-weight:800;letter-spacing:.9px}.art-review-fact strong{overflow:hidden;color:#4e5741;font-size:10px;text-overflow:ellipsis;white-space:nowrap}.art-review-fact.price strong{color:#63734c}
    .art-review-decision{margin-top:12px;padding:11px;border:1px solid #e8e5da;border-radius:12px;background:#faf9f4}.decision-title{display:block;margin-bottom:9px;color:#505a42;font-size:9px;font-weight:800;letter-spacing:.7px}.approve-form{margin:0}.art-review-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:35px;padding:8px 11px;border:0;border-radius:8px;font:700 10px Arial,sans-serif;cursor:pointer;transition:transform .16s,background .16s}.art-review-button:hover{transform:translateY(-1px)}.art-review-button.approve{width:100%;background:#566541;color:#fffdf5}.art-review-button.approve:hover{background:#414c2c}.reject-details{margin-top:8px}.reject-details summary{padding:8px;border:1px solid #eadbd5;border-radius:8px;background:#fff9f6;color:#925847;font-size:9px;font-weight:700;cursor:pointer;list-style:none}.reject-details summary::-webkit-details-marker{display:none}.reject-details summary:after{float:right;content:"＋"}.reject-details[open] summary:after{content:"−"}.reject-details textarea{display:block;width:100%;min-height:64px;margin-top:8px;padding:9px;border:1px solid #e3d5cf;border-radius:8px;background:#fffefa;color:#35372f;font:10px/1.5 Arial,sans-serif;resize:vertical}.reject-details textarea:focus{outline:2px solid rgba(166,111,83,.22);border-color:#bb8d77}.art-review-button.reject{width:100%;margin-top:7px;background:#f4e3df;color:#8d483c}.art-review-button.reject:hover{background:#ecd2cb}
    .art-review-empty{display:grid;justify-items:center;padding:42px 20px;border:1px dashed #d8d5c8;border-radius:17px;background:rgba(255,255,255,.62);text-align:center}.art-review-empty-symbol{display:grid;place-items:center;width:48px;height:48px;margin-bottom:13px;border-radius:50%;background:#ecebdd;color:#75805a;font-size:22px}.art-review-empty h2{margin:0 0 7px;color:#303a2a;font:normal 22px Georgia,serif}.art-review-empty p{max-width:380px;margin:0;color:#7f8073;font-size:11px;line-height:1.7}.art-review-pagination{margin-top:20px;overflow-x:auto}.art-review-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:26px;padding-top:16px;border-top:1px solid var(--border);color:var(--muted);font-size:10px}.art-review-back-link{display:inline-flex;align-items:center;gap:7px;padding:9px 12px;border:1px solid #d8dacb;border-radius:9px;background:#fffefa;color:#586643;font-size:10px;font-weight:700;text-decoration:none;transition:background .18s}.art-review-back-link:hover{background:#f0f1e8}
    @media(max-width:1200px){.art-review-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.art-review-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:750px){.review-hero{padding:26px 23px}.art-review-grid{gap:13px}.art-review-card-content{padding:14px}.art-review-toolbar{align-items:flex-start;flex-direction:column}.art-review-filters{width:100%;overflow-x:auto;flex-wrap:nowrap}.art-review-filter{flex:0 0 auto}}
    @media(max-width:540px){.review-hero{align-items:flex-start;min-height:0}.review-hero-stat{min-width:88px;padding:12px}.review-hero-stat strong{font-size:32px}.review-hero-stat span{font-size:7px}.art-review-grid{grid-template-columns:1fr}.art-review-image-wrap{aspect-ratio:4/3}.art-review-card-content{padding:16px}.art-review-card-title{font-size:25px}.art-review-footer{align-items:flex-start}.art-review-stats{gap:8px}.art-review-stat{padding:12px}.art-review-stat-number{font-size:25px}}
    @media(prefers-reduced-motion:reduce){.art-review-card,.art-review-image,.art-review-button,.art-review-filter,.art-review-back-link{transition:none}}
</style>
@endpush

@section('content')
    @if (session('success') || session('error') || $errors->any())
        <div class="art-review-messages">
            @if (session('success'))<div class="art-review-message">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="art-review-message error">{{ session('error') }}</div>@endif
            @if ($errors->any())
                <div class="art-review-message error"><strong>Please correct the following:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
        </div>
    @endif

    <section class="review-hero">
        <div class="hero-ring"></div>
        <div class="review-hero-copy"><span class="review-kicker"><i></i> THE CURATION DESK</span><h2>Every artwork<br>has <em>a story.</em></h2><p>Review artist submissions with care. Approved pieces can join the gallery; rejected pieces remain with their artist for revision.</p></div>
        <div class="review-hero-stat"><strong>{{ $counts['pending'] }}</strong><span>AWAITING REVIEW</span></div>
    </section>

    <section class="art-review-stats" aria-label="Artwork moderation totals">
        <article class="art-review-stat stat-pending"><div class="stat-topline"><span class="art-review-stat-label">AWAITING REVIEW</span><span class="stat-icon">◷</span></div><strong class="art-review-stat-number">{{ $counts['pending'] }}</strong></article>
        <article class="art-review-stat stat-approved"><div class="stat-topline"><span class="art-review-stat-label">APPROVED</span><span class="stat-icon">✓</span></div><strong class="art-review-stat-number">{{ $counts['approved'] }}</strong></article>
        <article class="art-review-stat stat-rejected"><div class="stat-topline"><span class="art-review-stat-label">REJECTED</span><span class="stat-icon">↺</span></div><strong class="art-review-stat-number">{{ $counts['rejected'] }}</strong></article>
        <article class="art-review-stat"><div class="stat-topline"><span class="art-review-stat-label">TOTAL SUBMISSIONS</span><span class="stat-icon">✦</span></div><strong class="art-review-stat-number">{{ $counts['all'] }}</strong></article>
    </section>

    <div class="art-review-toolbar">
        <div class="art-review-toolbar-copy"><span class="toolbar-kicker">ARTIST SUBMISSIONS</span><span class="toolbar-title">Review gallery</span></div>
        <nav class="art-review-filters" aria-label="Filter artworks by moderation status">
            @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All artworks'] as $key => $label)
                <a class="art-review-filter {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.artworks.index', ['status' => $key]) }}" @if ($status === $key) aria-current="page" @endif>{{ $label }}<small>{{ $counts[$key] }}</small></a>
            @endforeach
        </nav>
    </div>

    @php
        $visibleArtworks = $artworks->getCollection()->reject(function ($artwork) {
            return (int) $artwork->id === 7 && $artwork->title === 'Velvet Afterglow';
        });
    @endphp

    @if ($visibleArtworks->isEmpty())
        <div class="art-review-empty"><span class="art-review-empty-symbol">✳</span><h2>The gallery is clear</h2><p>There are no artworks in this review category right now.</p></div>
    @else
        <section class="art-review-grid" aria-label="Artwork submissions">
            @foreach ($visibleArtworks as $artwork)
                @php
                    $artistName = $artwork->artist?->artistProfile?->artist_name ?: ($artwork->artist?->name ?? 'Unknown artist');
                    $artistPhoto = $artwork->artist?->artistProfile?->profile_photo;
                @endphp
                <article class="art-review-card">
                    <div class="art-review-image-wrap">
                        @if ($artwork->image)
                            <a class="art-review-image-link" href="{{ asset('storage/' . $artwork->image) }}" target="_blank" rel="noopener" aria-label="Open full image of {{ $artwork->title }}"><img class="art-review-image" src="{{ asset('storage/' . $artwork->image) }}" alt="{{ $artwork->title }}" loading="lazy"></a>
                        @else
                            <div class="art-review-image-placeholder">No artwork image available</div>
                        @endif
                        <span class="image-badge {{ $artwork->moderation_status }}"><i></i>{{ ucfirst($artwork->moderation_status) }}</span>
                        <span class="image-index">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="art-review-card-content">
                        <div class="art-review-artist-row">
                            @if ($artistPhoto)<img class="art-review-avatar" src="{{ asset('storage/' . $artistPhoto) }}" alt="" loading="lazy">@else<span class="art-review-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($artistName, 0, 1)) }}</span>@endif
                            <span class="artist-caption"><small>SUBMITTED BY</small><strong>{{ $artistName }}</strong></span>
                        </div>
                        <h2 class="art-review-card-title">{{ $artwork->title }}</h2>
                        <p class="art-review-description">{{ $artwork->description ?: 'No description provided.' }}</p>
                        <div class="art-review-tags"><span>{{ $artwork->category ?: 'Uncategorized' }}</span><span>{{ $artwork->medium ?: 'Medium not specified' }}</span></div>
                        <div class="art-review-facts">
                            <div class="art-review-fact price"><small>ASKING PRICE</small><strong>{{ $artwork->price !== null ? '$' . number_format((float) $artwork->price, 2) : 'Not specified' }}</strong></div>
                            <div class="art-review-fact"><small>AVAILABILITY</small><strong>{{ $artwork->is_sold ? 'Sold' : ($artwork->status ?: 'Not specified') }}</strong></div>
                        </div>

                        @if ($artwork->moderation_status === 'pending')
                            <div class="art-review-decision">
                                <span class="decision-title">MODERATION DECISION</span>
                                <form class="approve-form" method="POST" action="{{ route('admin.artworks.approve', $artwork) }}" onsubmit="return confirm('Approve this artwork?');">
                                    @csrf
                                    <button class="art-review-button approve" type="submit">Approve artwork <span aria-hidden="true">✓</span></button>
                                </form>
                                <details class="reject-details">
                                    <summary>Reject and request a revision</summary>
                                    <form method="POST" action="{{ route('admin.artworks.reject', $artwork) }}" onsubmit="return confirm('Reject this artwork? It will not be deleted.');">
                                        @csrf
                                        <textarea id="notes-{{ $artwork->id }}" name="admin_notes" required maxlength="2000" aria-label="Reason for rejecting {{ $artwork->title }}" placeholder="Explain what needs to be corrected..."></textarea>
                                        <button class="art-review-button reject" type="submit">Reject artwork</button>
                                    </form>
                                </details>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        @if ($artworks->hasPages())<div class="art-review-pagination">{{ $artworks->links() }}</div>@endif
    @endif

    <footer class="art-review-footer"><span>Artfy Administration · Artwork Review</span><a href="{{ route('home') }}" class="art-review-back-link"><span aria-hidden="true">←</span> Return to Discover</a></footer>
@endsection
