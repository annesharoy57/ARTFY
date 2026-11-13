@extends('coadmin.layout')

@section('title', $exhibition->title . ' | Artfy Co-admin')

@section('content')
    @php
        $isOpen = $exhibition->visitIsOpen();
        $artistCount = $applications->count();
        $voteCount = $applications->sum('votes_count');
        $scoredCount = $applications->filter(fn ($entry) => $entry->scores->isNotEmpty())->count();
    @endphp

    <a class="back-link" href="{{ route('co-admin.exhibitions.index') }}"><span aria-hidden="true">←</span> All exhibitions</a>

    <section class="review-hero">
        <div class="hero-art" @if ($exhibition->cover_image) style="background-image:url('{{ asset('storage/' . $exhibition->cover_image) }}')" @endif></div>
        <div class="hero-wash"></div>
        <div class="hero-copy">
            <span class="hero-kicker"><i></i> CO-ADMIN JUDGING ROOM</span>
            <h1>{{ $exhibition->title }}</h1>
            @if ($exhibition->theme || $exhibition->subtitle)
                <p class="hero-theme">{{ $exhibition->theme ?: $exhibition->subtitle }}</p>
            @endif
            <p class="hero-description">{{ \Illuminate\Support\Str::limit(strip_tags($exhibition->description ?? 'Explore the artists and their work.'), 220) }}</p>
            <div class="hero-date"><span aria-hidden="true">▦</span><span>{{ $exhibition->starts_at->format('M j, Y') }} <b>—</b> {{ $exhibition->ends_at->format('M j, Y') }}</span></div>
        </div>
        <div class="judging-state {{ $isOpen ? 'state-open' : 'state-closed' }}"><i></i><div><strong>{{ $isOpen ? 'Scoring is open' : 'Exhibition closed' }}</strong><span>{{ $isOpen ? 'Your scores can be submitted or updated.' : 'Final scores are shown below.' }}</span></div></div>
        <span class="hero-ornament" aria-hidden="true">✳</span>
    </section>

    @if (session('success'))
        <div class="review-alert alert-success" role="status"><span>✓</span>{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="review-alert alert-error" role="alert"><span>!</span>{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="review-alert alert-error" role="alert"><span>!</span><div><strong>Score could not be saved.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
    @endif

    <section class="review-summary" aria-label="Judging summary">
        <div class="summary-card"><span class="summary-icon">✦</span><div><strong>{{ $artistCount }}</strong><span>PARTICIPATING ARTWORKS</span></div></div>
        <div class="summary-card"><span class="summary-icon vote-icon">♡</span><div><strong>{{ $voteCount }}</strong><span>COMMUNITY VOTES</span></div></div>
        <div class="summary-card"><span class="summary-icon score-icon">✓</span><div><strong>{{ $scoredCount }}<small> / {{ $artistCount }}</small></strong><span>YOUR SCORES SUBMITTED</span></div></div>
    </section>

    <div class="gallery-heading"><div><span class="section-kicker">THE COLLECTION</span><h2>Artist submissions</h2></div><p>Use your judging eye to score each work from 1 to 10.</p></div>

    <section class="submission-list" aria-label="Participant artwork">
        @forelse ($applications as $application)
            @php
                $artistName = $application->artist?->artistProfile?->artist_name ?: $application->artist?->name ?: 'Artfy Artist';
                $artistPhoto = $application->artist?->artistProfile?->profile_photo;
                $myScore = $application->scores->first()?->score;
            @endphp
            <article class="submission-card">
                <div class="submission-image">
                    @if ($application->artwork_image)
                        <img src="{{ asset('storage/' . $application->artwork_image) }}" alt="{{ $application->artwork_title }} by {{ $artistName }}" loading="lazy">
                    @else
                        <div class="image-fallback"><span>ARTWORK</span><i aria-hidden="true">✳</i></div>
                    @endif
                    <span class="artwork-number">WORK {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="submission-details">
                    <div class="artist-identity">
                        @if ($artistPhoto)
                            <img src="{{ asset('storage/' . $artistPhoto) }}" alt="" loading="lazy">
                        @else
                            <span class="artist-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($artistName, 0, 1)) }}</span>
                        @endif
                        <div><span class="artist-label">FEATURED ARTIST</span><strong>{{ $artistName }}</strong></div>
                    </div>
                    <h3>{{ $application->artwork_title }}</h3>
                    <p class="artwork-description">{{ $application->artwork_description ?: 'The artist has not added a description for this work yet.' }}</p>
                    <div class="artwork-facts">
                        <div><span class="fact-icon vote-fact">♡</span><span><small>COMMUNITY VOTES</small><strong>{{ $application->votes_count }}</strong></span></div>
                        <div><span class="fact-icon">৳</span><span><small>ASKING PRICE</small><strong>BDT {{ number_format((float) $application->artwork_price, 2) }}</strong></span></div>
                    </div>

                    @if ($isOpen)
                        <form method="POST" action="{{ route('co-admin.exhibitions.score', [$exhibition, $application]) }}" class="score-form">
                            @csrf
                            <fieldset>
                                <legend><span>Your judging score</span><small>Choose 1–10</small></legend>
                                <div class="score-options">
                                    @for ($score = 1; $score <= 10; $score++)
                                        <label class="score-option" for="score-{{ $application->id }}-{{ $score }}">
                                            <input id="score-{{ $application->id }}-{{ $score }}" type="radio" name="score" value="{{ $score }}" required @checked((string) $myScore === (string) $score)>
                                            <span>{{ $score }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </fieldset>
                            <div class="score-submit-row">
                                <p>{{ $myScore ? 'Your current score: ' . $myScore . ' / 10' : 'Your score helps determine the final awards.' }}</p>
                                <button type="submit">{{ $myScore ? 'Update score' : 'Submit score' }} <span aria-hidden="true">→</span></button>
                            </div>
                        </form>
                    @elseif ($myScore)
                        <div class="final-score"><span>YOUR FINAL SCORE</span><strong>{{ $myScore }}<small>/10</small></strong></div>
                    @else
                        <div class="score-closed-note">You did not submit a score during the judging period.</div>
                    @endif
                </div>
            </article>
        @empty
            <div class="empty-gallery"><span aria-hidden="true">✳</span><h3>The gallery is waiting for its artists</h3><p>No participant artworks have been submitted for this exhibition yet.</p></div>
        @endforelse
    </section>

    <style>
        .back-link{display:inline-flex;align-items:center;gap:9px;margin:0 0 17px;padding:9px 12px;border:1px solid #e2dfd2;border-radius:30px;background:rgba(255,254,249,.72);color:#586744;text-decoration:none;font-size:12px;font-weight:700;transition:background .2s,transform .2s}.back-link:hover{transform:translateX(-2px);background:#fffefa}.back-link span{font-size:17px}
        .review-hero{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:flex-end;min-height:330px;margin:0 0 22px;padding:35px 38px;border-radius:24px;background:linear-gradient(115deg,#414c2c,#596641);color:#fffdf5;box-shadow:0 22px 45px rgba(54,63,39,.15)}.hero-art,.hero-wash{position:absolute;z-index:-2;inset:0}.hero-art{background-size:cover;background-position:center;opacity:.28;filter:saturate(.7)}.hero-wash{z-index:-1;background:linear-gradient(90deg,rgba(48,57,35,.97) 0%,rgba(61,71,45,.9) 54%,rgba(59,69,43,.54) 100%)}.hero-copy{position:relative;z-index:1;max-width:700px}.hero-kicker{display:flex;align-items:center;gap:9px;color:#e3d4ae;font-size:9px;font-weight:800;letter-spacing:1.8px}.hero-kicker i{width:20px;height:1px;background:#ddbf7d}.hero-copy h1{margin:13px 0 5px;color:#fffdf5;font:500 clamp(35px,5vw,55px)/1.02 Georgia,serif;letter-spacing:-1.2px}.hero-theme{margin:0 0 11px;color:#e0ca93;font-size:10px;font-weight:800;letter-spacing:1.6px;text-transform:uppercase}.hero-description{max-width:620px;margin:13px 0;color:#e3e5db;font-size:13px;line-height:1.75}.hero-date{display:flex;align-items:center;gap:9px;margin-top:17px;color:#f4f0df;font-size:12px;font-weight:700}.hero-date>span:first-child{display:grid;place-items:center;width:30px;height:30px;border:1px solid rgba(255,255,255,.25);border-radius:9px;color:#e3cc92}.hero-date b{padding:0 3px;color:#dfc184}.judging-state{position:absolute;right:25px;top:24px;display:flex;align-items:center;gap:10px;padding:11px 14px;border:1px solid rgba(255,255,255,.22);border-radius:13px;background:rgba(255,255,255,.12);backdrop-filter:blur(8px)}.judging-state>i{width:8px;height:8px;border-radius:50%;background:#d5ad68}.judging-state.state-open>i{background:#b7d18c;box-shadow:0 0 0 4px rgba(183,209,140,.14)}.judging-state div{display:grid;gap:3px}.judging-state strong{font-size:11px}.judging-state div span{color:#e0e1d6;font-size:9px}.hero-ornament{position:absolute;right:16%;bottom:22%;color:rgba(229,207,156,.7);font-size:28px}
        .review-alert{display:flex;align-items:flex-start;gap:10px;margin:0 0 14px;padding:13px 15px;border:1px solid;border-radius:12px;font-size:12px;line-height:1.55}.review-alert>span{display:grid;place-items:center;flex:0 0 20px;height:20px;border-radius:50%;background:rgba(255,255,255,.7);font-weight:800}.review-alert ul{margin:4px 0 0;padding-left:18px}.alert-success{border-color:#d4e3c9;background:#f0f5e9;color:#47603a}.alert-error{border-color:#efd4cc;background:#fff1ed;color:#8b3e31}
        .review-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px;margin:0 0 34px}.summary-card{display:flex;align-items:center;gap:12px;padding:15px 17px;border:1px solid #e8e4d8;border-radius:15px;background:rgba(255,254,249,.88);box-shadow:0 8px 22px rgba(53,59,40,.035)}.summary-icon{display:grid;place-items:center;flex:0 0 38px;height:38px;border-radius:12px;background:#eff0e5;color:#67744e;font-size:18px}.vote-icon,.vote-fact{color:#a66d55}.summary-card>div{display:grid;gap:3px}.summary-card strong{color:#35412d;font:normal 25px/1 Georgia,serif}.summary-card strong small{color:#858674;font:12px Arial,sans-serif}.summary-card>div>span{color:#8a8b79;font-size:8px;font-weight:800;letter-spacing:1.1px}
        .gallery-heading{display:flex;align-items:end;justify-content:space-between;gap:18px;margin:0 2px 16px}.section-kicker{display:block;color:#8a8b76;font-size:9px;font-weight:800;letter-spacing:1.7px}.gallery-heading h2{margin:5px 0 0;color:#343d2b;font:normal 28px Georgia,serif}.gallery-heading>p{margin:0;color:#858575;font-size:11px}.submission-list{display:grid;gap:20px}.submission-card{display:grid;grid-template-columns:minmax(250px,.9fr) minmax(0,1.1fr);overflow:hidden;border:1px solid #e6e2d6;border-radius:21px;background:#fffefa;box-shadow:0 14px 35px rgba(55,56,40,.06);transition:transform .22s,box-shadow .22s}.submission-card:hover{transform:translateY(-3px);box-shadow:0 22px 42px rgba(55,56,40,.1)}.submission-image{position:relative;display:grid;place-items:center;min-height:410px;overflow:hidden;background:radial-gradient(circle at 75% 20%,rgba(255,255,255,.62),transparent 28%),linear-gradient(145deg,#e7e7d8,#d6dac8)}.submission-image img{display:block;width:100%;height:100%;min-height:410px;object-fit:cover;transition:transform .65s cubic-bezier(.2,.7,.2,1)}.submission-card:hover .submission-image img{transform:scale(1.035)}.artwork-number{position:absolute;left:16px;top:16px;padding:8px 10px;border:1px solid rgba(255,255,255,.7);border-radius:30px;background:rgba(255,254,249,.82);color:#586744;font-size:8px;font-weight:800;letter-spacing:1.2px;backdrop-filter:blur(7px)}.image-fallback{display:grid;place-items:center;width:100%;height:100%;min-height:410px;background:radial-gradient(circle at 70% 20%,rgba(255,255,255,.5),transparent 30%),linear-gradient(145deg,#c4c9ad,#8a9771)}.image-fallback span{color:rgba(255,255,255,.8);font:italic 35px Georgia,serif;letter-spacing:3px}.image-fallback i{position:absolute;right:20%;bottom:17%;color:rgba(255,255,255,.65);font-size:24px;font-style:normal}
        .submission-details{display:flex;flex-direction:column;padding:26px 29px}.artist-identity{display:flex;align-items:center;gap:11px}.artist-identity>img,.artist-avatar{display:grid;place-items:center;width:44px;height:44px;flex:0 0 44px;overflow:hidden;border:2px solid #fff;border-radius:50%;background:linear-gradient(145deg,#d9d7bb,#899572);color:#fffdf3;object-fit:cover;box-shadow:0 5px 14px rgba(65,76,44,.14)}.artist-avatar{font:19px Georgia,serif}.artist-identity>div{display:grid;gap:4px}.artist-label{color:#92917b;font-size:8px;font-weight:800;letter-spacing:1.4px}.artist-identity strong{color:#55613a;font-size:13px}.submission-details h3{margin:19px 0 8px;color:#34402c;font:normal clamp(26px,3vw,35px)/1.1 Georgia,serif;letter-spacing:-.5px}.artwork-description{display:-webkit-box;overflow:hidden;margin:0;color:#737766;font-size:12px;line-height:1.75;-webkit-box-orient:vertical;-webkit-line-clamp:4}.artwork-facts{display:flex;gap:23px;margin:19px 0 17px;padding:14px 0;border-top:1px solid #efede5;border-bottom:1px solid #efede5}.artwork-facts>div{display:flex;align-items:center;gap:9px}.fact-icon{display:grid;place-items:center;width:32px;height:32px;border-radius:10px;background:#eff0e6;color:#62704b;font-size:15px}.artwork-facts>div>span:last-child{display:grid;gap:4px}.artwork-facts small{color:#969584;font-size:8px;font-weight:800;letter-spacing:1px}.artwork-facts strong{color:#4b513d;font-size:11px}.score-form{margin-top:auto;padding:15px;border:1px solid #e9e6da;border-radius:14px;background:linear-gradient(145deg,#fbfaf4,#f4f3e9)}.score-form fieldset{min-width:0;margin:0;padding:0;border:0}.score-form legend{display:flex;align-items:center;justify-content:space-between;width:100%;margin-bottom:11px;color:#4e5940;font-size:11px;font-weight:800}.score-form legend small{color:#989785;font-size:9px;font-weight:500}.score-options{display:grid;grid-template-columns:repeat(10,minmax(0,1fr));gap:5px}.score-option{cursor:pointer}.score-option input{position:absolute;width:1px;height:1px;opacity:0}.score-option span{display:grid;place-items:center;min-height:31px;border:1px solid #dedccd;border-radius:8px;background:#fffefa;color:#77796a;font-size:10px;font-weight:700;transition:background .16s,color .16s,border-color .16s,transform .16s}.score-option:hover span{transform:translateY(-2px);border-color:#9da783}.score-option input:checked+span{border-color:#586744;background:#586744;color:#fffdf5;box-shadow:0 4px 9px rgba(75,88,55,.18)}.score-option input:focus-visible+span{outline:3px solid rgba(151,160,113,.4);outline-offset:2px}.score-submit-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:13px}.score-submit-row p{margin:0;color:#858575;font-size:9px;line-height:1.5}.score-submit-row button{display:inline-flex;align-items:center;gap:10px;flex:0 0 auto;padding:10px 13px;border:0;border-radius:9px;background:#566541;color:#fffdf6;font-size:10px;font-weight:800;cursor:pointer;transition:transform .18s,background .18s}.score-submit-row button:hover{transform:translateY(-1px);background:#414c2c}.score-submit-row button span{font-size:15px}.final-score{display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding:15px 17px;border:1px solid #dce4cf;border-radius:13px;background:#f1f5eb}.final-score>span{color:#6c7657;font-size:9px;font-weight:800;letter-spacing:1.1px}.final-score strong{color:#425336;font:normal 27px Georgia,serif}.final-score small{margin-left:3px;color:#899078;font:12px Arial,sans-serif}.score-closed-note{margin-top:auto;padding:14px;border-radius:11px;background:#f3f1e8;color:#858474;font-size:11px}.empty-gallery{display:grid;justify-items:center;padding:45px 20px;border:1px dashed #d9d5c7;border-radius:18px;background:rgba(255,255,255,.6);text-align:center}.empty-gallery>span{display:grid;place-items:center;width:48px;height:48px;border-radius:50%;background:#ecebdd;color:#75805a;font-size:22px}.empty-gallery h3{margin:13px 0 5px;color:#39422d;font:normal 22px Georgia,serif}.empty-gallery p{margin:0;color:#7e7e72;font-size:12px;line-height:1.65}
        @media(max-width:900px){.review-hero{min-height:315px;padding:30px}.submission-card{grid-template-columns:minmax(220px,.8fr) minmax(0,1.2fr)}.submission-image,.submission-image img,.image-fallback{min-height:350px}.submission-details{padding:22px}.artwork-facts{gap:14px}}
        @media(max-width:680px){.review-hero{align-items:flex-start;min-height:360px;padding:83px 23px 25px}.judging-state{top:17px;left:18px;right:auto}.hero-copy h1{font-size:39px}.review-summary{grid-template-columns:1fr;gap:9px;margin-bottom:27px}.summary-card{padding:12px 14px}.summary-icon{width:34px;height:34px;flex-basis:34px}.gallery-heading{align-items:flex-start;flex-direction:column;gap:5px}.gallery-heading h2{font-size:26px}.submission-card{grid-template-columns:1fr}.submission-image,.submission-image img,.image-fallback{min-height:280px;height:320px}.submission-details{padding:20px}.score-options{gap:4px}.score-option span{min-height:34px}}
        @media(max-width:390px){.score-options{grid-template-columns:repeat(5,minmax(0,1fr))}.score-submit-row{align-items:flex-start;flex-direction:column}.score-submit-row button{width:100%;justify-content:center}.artwork-facts{justify-content:space-between;gap:7px}.artwork-facts strong{font-size:10px}}
        @media(prefers-reduced-motion:reduce){.back-link,.submission-card,.submission-image img,.score-option span,.score-submit-row button{transition:none}}
    </style>
@endsection
