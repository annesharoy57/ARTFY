@extends(auth()->check() && auth()->user()->role === 'admin' ? 'admin.layout' : 'artfy.layout')

@section('title', $exhibition->title . ' | Artfy Exhibitions')
@section('page_eyebrow', 'Artfy / Exhibition Management')
@section('page_title', 'Online exhibition preview')
@section('page_intro', 'Public exhibition details and participating artists.')

@section('content')
<main class="exhibition-page">
    @php
        $adminPreview = auth()->check() && auth()->user()->role === 'admin';
    @endphp

    @if (session('success'))
        <p class="exhibition-alert success" role="status">{{ session('success') }}</p>
    @endif
    @if (session('error'))
        <p class="exhibition-alert error" role="alert">{{ session('error') }}</p>
    @endif
    @if ($errors->any())
        <div class="exhibition-alert error" role="alert">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <a class="exhibition-back" href="{{ $adminPreview ? route('admin.exhibitions.index') : route('exhibitions.index') }}">
        {{ $adminPreview ? '← Back to admin exhibitions' : '← All exhibitions' }}
    </a>
    <header class="exhibition-header" style="--exhibition-accent: {{ $exhibition->accent_color ?: '#55613a' }}">
        <div class="exhibition-copy">
            <p class="exhibition-eyebrow"><span></span> ONLINE EXHIBITION</p>
            <h1>{{ $exhibition->title }}</h1>
            @if ($exhibition->subtitle)
                <p class="exhibition-subtitle">{{ $exhibition->subtitle }}</p>
            @endif
            @if ($exhibition->theme)
                <p class="exhibition-theme">{{ $exhibition->theme }}</p>
            @endif
            <p class="exhibition-description">{{ $exhibition->description }}</p>
            <div class="exhibition-hero-dates">
                <span>EXHIBITION DATES</span>
                <strong>{{ $exhibition->starts_at->format('M j') }} — {{ $exhibition->ends_at->format('M j, Y') }}</strong>
            </div>
        </div>
        <div class="exhibition-poster-stage" aria-label="Exhibition poster">
            <div class="exhibition-poster-shadow" aria-hidden="true"></div>
            <figure class="exhibition-poster-card">
                @if ($exhibition->cover_image)
                    <img src="{{ asset('storage/' . $exhibition->cover_image) }}" alt="{{ $exhibition->title }} exhibition poster">
                @else
                    <div class="exhibition-poster-fallback">
                        <span>ARTFY · ONLINE EXHIBITION</span>
                        <strong>{{ $exhibition->title }}</strong>
                        @if ($exhibition->theme)<small>{{ $exhibition->theme }}</small>@endif
                    </div>
                @endif
            </figure>
            <span class="exhibition-poster-caption">CURATED FOR ART LOVERS</span>
        </div>
    </header>

    <section class="exhibition-facts" aria-label="Exhibition information">
        <div><span>Exhibition dates</span><strong>{{ $exhibition->starts_at->format('M j, Y') }} – {{ $exhibition->ends_at->format('M j, Y') }}</strong></div>
        <div><span>Registration deadline</span><strong>{{ $exhibition->registration_closes_at->format('M j, Y, g:i A') }}</strong></div>
        <div><span>Registration fee</span><strong>৳{{ number_format((float) $exhibition->registration_fee, 2) }}</strong></div>
        <div>
            <span>Artists enrolled</span>
            <strong>{{ $enrolledCount }} of {{ $exhibition->max_artists }}</strong>
            @if ($pendingPaymentCount > 0)
                <small>
                    {{ $pendingPaymentCount }} pending registration
                    {{ $pendingPaymentCount === 1 ? 'payment does' : 'payments do' }}
                    not count until verified.
                </small>
            @endif
        </div>
    </section>

    <section class="exhibition-visit-status">
        @if ($visitOpen)
            <strong>Exhibition is open for visits, artist and Art Lover voting, judging, and artwork purchases.</strong>
        @elseif (! $exhibition->visitHasStarted())
            <strong>The gallery, voting, judging, and buying open when the exhibition begins.</strong>
        @else
            <strong>The exhibition is closed for visits, voting, judging, and artwork purchases.</strong>
        @endif
    </section>

    @unless ($adminPreview)
    @auth
        @if (auth()->user()->role === 'artist')
            <section class="exhibition-apply">
                @if (
                    $existingApplication?->payment_status === 'verified'
                    && (
                        $existingApplication?->status === 'awaiting_submission'
                        || (
                            $existingApplication?->status === 'confirmed'
                            && $registrationWindowOpen
                        )
                    )
                )
                    @php
                        $editingArtwork = $existingApplication->status === 'confirmed';
                    @endphp
                    <h2>{{ $editingArtwork ? 'Edit your exhibition artwork' : 'Submit your exhibition artwork' }}</h2>
                    <p>
                        {{ $editingArtwork
                            ? 'You can update your artwork until exhibition applications close.'
                            : 'Your registration fee is verified. Complete this form to finalize your application.' }}
                    </p>
                    <form
                        class="exhibition-artwork-form"
                        method="POST"
                        action="{{ $editingArtwork
                            ? route('exhibitions.update-artwork', $exhibition)
                            : route('exhibitions.submit-artwork', $exhibition) }}"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @if ($editingArtwork)
                            @method('PUT')
                        @endif
                        <label>
                            Artwork title
                            <input type="text" name="artwork_title" value="{{ old('artwork_title', $existingApplication->artwork_title) }}" maxlength="180" required>
                        </label>
                        <label>
                            Artwork description
                            <textarea name="artwork_description" rows="5" maxlength="5000" required>{{ old('artwork_description', $existingApplication->artwork_description) }}</textarea>
                        </label>
                        <label>
                            Asking price (BDT)
                            <input type="number" name="artwork_price" min="0" max="9999999999.99" step="0.01" value="{{ old('artwork_price', $existingApplication->artwork_price) }}" required>
                        </label>
                        <label>
                            Artwork format
                            <select id="exhibition-artwork-type" name="artwork_type" required>
                                <option value="physical" @selected(old('artwork_type', $existingApplication->artwork_type ?: 'physical') === 'physical')>Physical · delivered to buyer</option>
                                <option value="digital" @selected(old('artwork_type', $existingApplication->artwork_type ?: 'physical') === 'digital')>Digital · PDF download</option>
                            </select>
                        </label>
                        <label>
                            Artwork picture (JPG, PNG, or WebP; up to 5 MB)
                            @if ($editingArtwork && $existingApplication->artwork_image)
                                <img
                                    class="exhibition-edit-image"
                                    src="{{ asset('storage/' . $existingApplication->artwork_image) }}"
                                    alt="{{ $existingApplication->artwork_title }}"
                                >
                                <span>Choose a new picture only if you want to replace the current one.</span>
                            @endif
                            <input type="file" name="artwork_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" @required(!$editingArtwork)>
                        </label>
                        <label id="exhibition-digital-file-field" @if(old('artwork_type', $existingApplication->artwork_type ?: 'physical') !== 'digital') hidden @endif>
                            Artwork PDF (up to 20 MB)
                            @if ($editingArtwork && $existingApplication->digital_file_path)
                                <span>A private PDF is already stored. Upload another only to replace it.</span>
                            @endif
                            <input type="file" name="digital_file" accept=".pdf,application/pdf" @required(!$editingArtwork && old('artwork_type', $existingApplication->artwork_type ?: 'physical') === 'digital')>
                        </label>
                        <button class="exhibition-button" type="submit">
                            {{ $editingArtwork ? 'Save artwork changes' : 'Submit artwork and finalize application' }}
                        </button>
                    </form>
                @elseif (
                    $existingApplication?->payment_status === 'verified'
                    && $existingApplication?->status === 'confirmed'
                )
                    <p class="exhibition-confirmed">
                        Your exhibition application is complete. Your artwork is shown below.
                        Artwork editing is only available while exhibition applications are open.
                    </p>
                @elseif (
                    $existingApplication?->payment_status === 'verified'
                    && $existingApplication?->status === 'capacity_review'
                )
                    <p class="exhibition-alert error" role="alert">
                        Your payment was received, but all exhibition places were paid for before it completed.
                        Please contact Artfy support to resolve your registration.
                    </p>
                @elseif ($existingApplication?->status === 'cancelled')
                    @if ($registrationOpen)
                        <form method="POST" action="{{ route('exhibitions.apply', $exhibition) }}">
                            @csrf
                            <button class="exhibition-button" type="submit">Reapply and pay ৳{{ number_format((float) $exhibition->registration_fee, 2) }}</button>
                        </form>
                    @else
                        <p>Applications are currently closed.</p>
                    @endif
                @elseif ($existingApplication)
                    @if (
                        $exhibition->status === 'published'
                        && (
                            $existingApplication->isActivePaymentReservation()
                            || $registrationOpen
                        )
                    )
                        <form method="POST" action="{{ route('exhibitions.apply', $exhibition) }}">
                            @csrf
                            <button class="exhibition-button" type="submit">
                                {{ $existingApplication->isActivePaymentReservation() ? 'Continue payment' : 'Retry payment' }}
                                · ৳{{ number_format((float) $existingApplication->registration_fee, 2) }}
                            </button>
                        </form>
                    @elseif ($existingApplication->status === 'pending')
                        <p>Your checkout reservation expired. You can retry if registration is still open and a place remains available. Contact Artfy support if you already completed payment.</p>
                    @else
                        <p>Your payment is pending. Contact Artfy support to resume checkout.</p>
                    @endif
                @elseif ($registrationOpen)
                    <form method="POST" action="{{ route('exhibitions.apply', $exhibition) }}">
                        @csrf
                        <button class="exhibition-button" type="submit">Apply and pay ৳{{ number_format((float) $exhibition->registration_fee, 2) }}</button>
                    </form>
                @else
                    <p>Applications are closed, or the paid artist capacity has been reached.</p>
                @endif
            </section>
        @elseif ($registrationOpen)
            <section class="exhibition-apply">
                <p>
                    You’re signed in with a
                    {{ str_replace('_', ' ', auth()->user()->role) }} account.
                    Only artist accounts can enroll in this exhibition.
                </p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="exhibition-button" type="submit">
                        Sign out to switch to an artist account
                    </button>
                </form>
            </section>
        @endif
    @else
        @if ($registrationOpen)
            <section class="exhibition-apply">
                <p>Sign in with an artist account to apply.</p>
                <a class="exhibition-button" href="{{ route('login', ['redirect' => url()->current()]) }}">Log in to apply</a>
            </section>
        @endif
    @endauth
    @endunless

    @if (! $galleryOpen)
        <section class="exhibition-gallery-locked" aria-live="polite">
            <div class="exhibition-gallery-locked-copy">
                <span>THE EXHIBITION OPENS SOON</span>
                <h2>The gallery will open its doors on {{ $exhibition->starts_at->format('M j, Y') }}.</h2>
                <p>Artists can register and submit their work now. The artwork gallery, voting, and buying will become available when the exhibition begins.</p>
            </div>
            <div class="exhibition-gallery-opening-card" aria-hidden="true">
                <span>GALLERY OPENS</span>
                <strong>{{ $exhibition->starts_at->format('M j') }}</strong>
                <small>{{ $exhibition->starts_at->format('Y · g:i A') }}</small>
            </div>
        </section>
    @else
    <section class="exhibition-artists">
        <div class="exhibition-gallery-heading">
            <span>THE EXHIBITION COLLECTION</span>
            <h2>Art made for this moment.</h2>
            <p>Explore every submitted work. Artists and Art Lovers can vote while the exhibition is open; co-admin judges score each artwork from 1 to 10. The top three artists receive prize money after judging closes.</p>
        </div>
        @php
            $participants = $exhibition->applications->filter(
                fn ($application) => $application->status === 'confirmed'
            );
        @endphp
        @forelse ($participants as $application)
            @php
                $artist = $application->artist;
                $artistName = $artist?->artistProfile?->artist_name ?: $artist?->name;
            @endphp
            <article class="exhibition-artist exhibition-artwork-card {{ $participants->count() === 1 ? 'exhibition-artwork-card-featured' : '' }}">
                <div class="exhibition-artwork-frame">
                    @if ($application->artwork_image)
                        <img
                            class="exhibition-submitted-image"
                            src="{{ asset('storage/' . ltrim($application->artwork_image, '/')) }}"
                            alt="{{ $application->artwork_title }} by {{ $artistName }}"
                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                            onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                        >
                        <div class="exhibition-image-unavailable" hidden><span>ARTWORK PREVIEW</span><strong>{{ $application->artwork_title }}</strong><small>The submitted image could not be loaded.</small></div>
                    @else
                        <div class="exhibition-artwork-empty" aria-hidden="true"><span>ARTFY / EXHIBITION WORK</span><strong>{{ $application->artwork_title }}</strong></div>
                    @endif
                    <div class="exhibition-vote-widget">
                        <span class="exhibition-vote-count" aria-label="{{ $application->votes_count }} votes">{{ $application->votes_count }}</span>
                        @unless ($adminPreview)
                        @if (auth()->check() && (auth()->user()->isArtLover() || auth()->user()->isArtist()))
                            @if ((int) auth()->id() === (int) $application->user_id)
                                <button class="exhibition-heart is-disabled" type="button" disabled aria-label="You cannot vote for your own artwork" title="You cannot vote for your own artwork">&#9825;</button>
                            @elseif ($application->voted_by_viewer)
                                <span class="exhibition-heart is-voted" role="status" aria-label="Your vote is recorded" title="Your vote is recorded">&#9829;</span>
                            @elseif (! $visitOpen)
                                <button class="exhibition-heart is-disabled" type="button" disabled aria-label="Voting opens when the exhibition starts" title="Voting opens when the exhibition starts">&#9825;</button>
                            @else
                                <form method="POST" action="{{ route('exhibitions.vote', [$exhibition, $application]) }}">
                                    @csrf
                                    <button class="exhibition-heart" type="submit" aria-label="Vote for {{ $application->artwork_title }}" title="Vote for this artwork">&#9825;</button>
                                </form>
                            @endif
                        @elseif (! auth()->check())
                            <a class="exhibition-heart" href="{{ route('login', ['role' => 'art_lover', 'redirect' => url()->current()]) }}" aria-label="Log in as an Art Lover to vote" title="Log in to vote">&#9825;</a>
                        @else
                            <button class="exhibition-heart is-disabled" type="button" disabled aria-label="Voting is for artist and Art Lover accounts" title="Voting is for artist and Art Lover accounts">&#9825;</button>
                        @endif
                        @endunless
                    </div>
                    @if ($resultsVisible && $application->rank <= 3)
                        <span class="exhibition-place-badge place-{{ $application->rank }}">{{ $application->rank === 1 ? '1st' : ($application->rank === 2 ? '2nd' : '3rd') }} place</span>
                    @endif
                </div>
                <div class="exhibition-artwork-copy">
                    <div class="exhibition-artist-identity">
                        @if ($artist?->artistProfile?->profile_photo)
                            <img src="{{ asset('storage/' . $artist->artistProfile->profile_photo) }}" alt="{{ $artistName }}" loading="lazy">
                        @else
                            <span class="exhibition-artist-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($artistName ?: 'A', 0, 1)) }}</span>
                        @endif
                        <div><span class="exhibition-artist-label">ARTIST</span><h3>{{ $artistName }}</h3></div>
                    </div>
                    <h4>{{ $application->artwork_title }}</h4>
                    <span class="exhibition-artwork-format">{{ ($application->artwork_type ?? 'physical') === 'digital' ? 'DIGITAL PDF' : 'PHYSICAL DELIVERY' }}</span>
                    <p class="exhibition-artwork-description">{{ $application->artwork_description }}</p>
                    <p class="exhibition-artwork-price">Asking price <strong>BDT {{ number_format((float) $application->artwork_price, 2) }}</strong></p>
                    @if ($resultsVisible)
                        <p class="exhibition-result">
                            <span>Total judging points</span>
                            <strong>{{ $application->total_points }}</strong>
                            <small>{{ $application->votes_count }} community votes + {{ (int) ($application->scores_sum_score ?? 0) }} co-admin points</small>
                        </p>
                    @endif
                    <div class="exhibition-art-actions">
                        @if ($application->sold)
                            <span class="exhibition-sold">Sold</span>
                        @elseif ($application->purchase_pending)
                            <span class="exhibition-purchase-pending">Payment confirmation in progress</span>
                        @elseif ($visitOpen && (float) $application->artwork_price > 0 && auth()->check() && auth()->user()->isArtLover())
                            <a class="exhibition-button" href="{{ route('artworks.purchase.create', ['slug' => 'exhibition-' . $application->id]) }}">Buy this artwork</a>
                        @endif
                    </div>
                    @if ($visitOpen && ! auth()->check() && (float) $application->artwork_price > 0 && ! $application->sold && ! $application->purchase_pending)
                        <a class="exhibition-buy-login" href="{{ route('login', ['role' => 'art_lover']) }}">Log in as an Art Lover to buy →</a>
                    @endif
                </div>
            </article>
        @empty
            <p class="exhibition-gallery-empty">Submitted artworks will appear here after the artists complete registration.</p>
        @endforelse
    </section>
    @endif
</main>

<style>
    .exhibition-page{width:min(1120px,100%);margin:auto;padding:44px 24px 70px;color:#292d22}
    .exhibition-back{color:#55613a;text-decoration:none;font-weight:600}
    .exhibition-header{position:relative;display:grid;grid-template-columns:minmax(0,1.12fr) minmax(280px,.88fr);align-items:center;gap:clamp(22px,5vw,64px);min-height:500px;margin:22px 0 28px;padding:clamp(28px,5.4vw,72px);overflow:hidden;border:1px solid rgba(255,255,255,.12);border-radius:18px;background:radial-gradient(ellipse at 82% 48%,color-mix(in srgb,var(--exhibition-accent) 54%,#292921),transparent 47%),linear-gradient(125deg,#171a16 0%,#242820 55%,#171914 100%);color:#fff;isolation:isolate;box-shadow:0 26px 64px rgba(25,29,20,.2)}
    .exhibition-header::before{position:absolute;z-index:-1;top:-190px;right:10%;width:520px;height:520px;border:1px solid rgba(255,255,255,.1);border-radius:50%;content:"";box-shadow:0 0 0 36px rgba(255,255,255,.025),0 0 0 80px rgba(255,255,255,.018)}
    .exhibition-copy{position:relative;z-index:1;max-width:610px}
    .exhibition-eyebrow{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.68);font-size:.72rem;font-weight:700;letter-spacing:.18em}
    .exhibition-eyebrow span{width:26px;height:1px;background:var(--exhibition-accent)}
    .exhibition-header h1{max-width:700px;margin:18px 0 10px;color:#fff;font:500 clamp(2.5rem,5vw,4.6rem)/.98 var(--serif);letter-spacing:-.045em}
    .exhibition-subtitle{margin:0 0 12px;color:rgba(255,255,255,.77);font-size:1.15rem}
    .exhibition-theme{margin:18px 0;color:color-mix(in srgb,var(--exhibition-accent) 62%,white);font-size:.8rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
    .exhibition-description{max-width:580px;margin:22px 0 0;white-space:pre-line;line-height:1.8;color:rgba(255,255,255,.79)}
    .exhibition-hero-dates{display:grid;gap:6px;margin-top:26px;padding-left:13px;border-left:2px solid var(--exhibition-accent)}
    .exhibition-hero-dates span{color:rgba(255,255,255,.56);font-size:.65rem;font-weight:700;letter-spacing:.16em}
    .exhibition-hero-dates strong{color:#fff;font-size:.95rem;font-weight:600}
    .exhibition-poster-stage{position:relative;display:grid;min-width:0;min-height:420px;place-items:center;perspective:1200px}
    .exhibition-poster-shadow{position:absolute;width:72%;height:76%;transform:translate(20px,17px) rotate(5deg);border:1px solid rgba(255,255,255,.16);border-radius:4px;background:linear-gradient(145deg,rgba(255,255,255,.12),rgba(255,255,255,.015));box-shadow:0 35px 45px rgba(0,0,0,.2)}
    .exhibition-poster-card{position:relative;z-index:1;display:grid;width:min(100%,370px);height:430px;place-items:center;margin:0;padding:12px;border:1px solid rgba(255,255,255,.4);border-radius:4px;background:#f3f0e8;box-shadow:0 30px 55px rgba(0,0,0,.42),inset 0 0 0 4px rgba(255,255,255,.45);transform:rotateY(-8deg) rotateX(2deg) rotateZ(1deg);transform-style:preserve-3d;transition:transform .45s cubic-bezier(.2,.75,.25,1),box-shadow .45s ease}
    .exhibition-poster-card img{display:block;width:100%;height:100%;object-fit:contain}
    .exhibition-header:hover .exhibition-poster-card{transform:rotateY(-3deg) rotateX(0) rotateZ(0);box-shadow:0 38px 65px rgba(0,0,0,.48),inset 0 0 0 4px rgba(255,255,255,.45)}
    .exhibition-poster-caption{position:absolute;right:0;bottom:10px;color:rgba(255,255,255,.55);font-size:.59rem;font-weight:700;letter-spacing:.17em}
    .exhibition-poster-fallback{display:flex;width:100%;height:100%;flex-direction:column;align-items:center;justify-content:center;gap:18px;padding:24px;background:radial-gradient(circle at 80% 18%,color-mix(in srgb,var(--exhibition-accent) 25%,white),transparent 36%),linear-gradient(145deg,#2b3026,#171914);color:#fff;text-align:center}
    .exhibition-poster-fallback span,.exhibition-poster-fallback small{color:rgba(255,255,255,.65);font-size:.62rem;letter-spacing:.14em;text-transform:uppercase}
    .exhibition-poster-fallback strong{font:400 2rem/1.1 var(--serif)}
    .exhibition-facts{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
    .exhibition-facts>div,.exhibition-apply,.exhibition-artist{padding:20px;border:1px solid #e5e2d6;border-radius:12px;background:#fff}
    .exhibition-facts span,.exhibition-facts strong{display:block}
    .exhibition-facts span{margin-bottom:8px;color:#72795f;font-size:.83rem}
    .exhibition-apply{margin-top:20px}
    .exhibition-button{display:inline-block;border:0;border-radius:7px;padding:13px 19px;background:#414c2c;color:#fff;text-decoration:none;font-weight:700;cursor:pointer}
    .exhibition-confirmed{color:#285b31;font-weight:700}
    .exhibition-artists{margin-top:52px}
    .exhibition-artists h2{font:600 2rem var(--serif)}
    .exhibition-artist{margin:16px 0}
    .exhibition-visit-status{margin:18px 0;padding:14px 18px;border:1px solid #e5e2d6;border-radius:10px;background:#fff}
    .exhibition-art-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:16px}
    .exhibition-art-actions form{margin:0}.exhibition-voted{color:#285b31;font-weight:700}.exhibition-sold{color:#922f32;font-weight:700}.exhibition-purchase-pending{display:inline-flex;padding:8px 11px;border:1px solid #e6d5a8;border-radius:999px;background:#fff8e7;color:#806731;font-size:.78rem;font-weight:700}
    .exhibition-result{padding:10px 12px;border-radius:8px;background:#f3f1e9}
    .exhibition-artwork-form{display:grid;gap:14px;max-width:680px;margin-top:18px}
    .exhibition-artwork-form label{display:grid;gap:7px;font-weight:600}
    .exhibition-artwork-form label[hidden]{display:none!important}
    .exhibition-artwork-form input,.exhibition-artwork-form textarea{width:100%;padding:11px;border:1px solid #e5e2d6;border-radius:7px;background:#fff;font:inherit}
    .exhibition-artwork-form input[type=file]{padding:9px}
    .exhibition-edit-image{display:block;width:min(100%,360px);max-height:300px;object-fit:contain;background:#f5f3e8}
    .exhibition-artwork-form textarea{resize:vertical}
    .exhibition-submitted-image{display:block;width:min(100%,560px);max-height:520px;object-fit:contain;background:#f5f3e8}
    .exhibition-artwork-price{font-weight:700;color:#55613a}
    .exhibition-alert{padding:12px 16px;border-radius:8px}
    .exhibition-alert.success{background:#e5f3e5;color:#285b31}
    .exhibition-alert.error{background:#fbe8e6;color:#922f32}
    @media(max-width:760px){.exhibition-header{grid-template-columns:1fr;gap:20px}.exhibition-poster-stage{min-height:350px}.exhibition-poster-card{height:min(112vw,390px);max-width:300px}.exhibition-poster-caption{right:8px;bottom:0}.exhibition-header h1{font-size:clamp(2.6rem,12vw,4rem)}}
    @media(max-width:700px){.exhibition-facts{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(prefers-reduced-motion:reduce){.exhibition-poster-card{transition:none}}

    /* Sculptural poster stage: layered gallery plinth with a floating 3D print. */
    .exhibition-header{min-height:590px;grid-template-columns:minmax(0,1.05fr) minmax(320px,.95fr);gap:clamp(28px,6vw,82px);padding:clamp(34px,6vw,84px);border:1px solid rgba(220,194,139,.22);border-radius:26px;background:radial-gradient(ellipse at 77% 46%,color-mix(in srgb,var(--exhibition-accent) 48%,#b79b64) 0%,transparent 34%),radial-gradient(ellipse at 15% 100%,rgba(126,141,85,.22),transparent 42%),linear-gradient(135deg,#111713,#22291f 58%,#161b16);box-shadow:0 38px 90px rgba(18,23,16,.28),inset 0 1px rgba(255,255,255,.08)}
    .exhibition-header::before{top:-170px;right:4%;width:610px;height:610px;border-color:rgba(230,210,163,.16);box-shadow:0 0 0 18px rgba(230,210,163,.025),0 0 0 58px rgba(230,210,163,.022),0 0 0 110px rgba(230,210,163,.015)}
    .exhibition-copy{max-width:640px}
    .exhibition-eyebrow{color:#d2c9ad}
    .exhibition-eyebrow span{width:34px;height:2px;background:#c6a66b;box-shadow:0 0 14px rgba(198,166,107,.7)}
    .exhibition-header h1{text-wrap:balance;text-shadow:0 5px 25px rgba(0,0,0,.22)}
    .exhibition-theme{color:#c6b986;letter-spacing:.17em}
    .exhibition-description{max-width:540px;font-size:1.03rem;line-height:1.85;color:rgba(250,248,239,.78)}
    .exhibition-poster-stage{min-height:490px;perspective:1500px;transform-style:preserve-3d}
    .exhibition-poster-stage::before{position:absolute;inset:11% 5%;border:1px solid rgba(228,207,159,.13);border-radius:50%;content:"";transform:rotate(-19deg) scaleY(.42);box-shadow:0 0 50px rgba(198,166,107,.08)}
    .exhibition-poster-shadow{width:72%;height:78%;transform:translate(25px,22px) rotateY(-15deg) rotateZ(7deg) translateZ(-34px);border:1px solid rgba(228,207,159,.45);border-radius:8px;background:linear-gradient(145deg,rgba(217,194,143,.24),rgba(255,255,255,.025));box-shadow:0 45px 65px rgba(0,0,0,.38),inset 0 0 0 8px rgba(255,255,255,.025)}
    .exhibition-poster-card{width:min(82%,390px);height:470px;padding:12px;border:1px solid rgba(255,255,255,.75);border-radius:9px;background:linear-gradient(145deg,#fffdf6,#dfd9c8);box-shadow:0 45px 75px rgba(0,0,0,.48),0 0 0 1px rgba(198,166,107,.5),inset 0 0 0 5px rgba(255,255,255,.65);transform:rotateY(-14deg) rotateX(4deg) rotateZ(2deg);transform-origin:50% 55%}
    .exhibition-poster-card::after{position:absolute;inset:12px;border:1px solid rgba(49,52,39,.14);border-radius:3px;content:"";pointer-events:none}
    .exhibition-poster-card img{border-radius:2px;transition:transform .7s cubic-bezier(.2,.7,.2,1);transform:translateZ(8px)}
    .exhibition-header:hover .exhibition-poster-card{transform:rotateY(-5deg) rotateX(0) rotateZ(0) translateY(-8px);box-shadow:0 55px 85px rgba(0,0,0,.52),0 0 0 1px rgba(198,166,107,.65),inset 0 0 0 5px rgba(255,255,255,.65)}
    .exhibition-header:hover .exhibition-poster-card img{transform:translateZ(18px) scale(1.015)}
    .exhibition-poster-caption{right:2%;bottom:4px;color:#d5c79f}
    .exhibition-facts>div{border-color:#e6e0d1;border-radius:16px;box-shadow:0 8px 24px rgba(41,45,34,.045);transition:transform .25s,box-shadow .25s}
    .exhibition-facts>div:hover{transform:translateY(-4px);box-shadow:0 15px 30px rgba(41,45,34,.1)}
    @media(max-width:760px){.exhibition-header{min-height:0;padding:34px 25px}.exhibition-poster-stage{min-height:410px}.exhibition-poster-card{width:min(75%,330px);height:min(112vw,410px)}}
    @media(prefers-reduced-motion:reduce){.exhibition-poster-card,.exhibition-poster-card img,.exhibition-facts>div{transition:none}}

    /* Ivory gallery room with a softly lit, sculptural exhibition poster. */
    .exhibition-header{border-color:rgba(105,119,74,.18);background:radial-gradient(ellipse at 76% 44%,rgba(215,199,151,.64),transparent 37%),radial-gradient(ellipse at 12% 100%,rgba(199,211,176,.62),transparent 45%),linear-gradient(135deg,#edf0e2,#f7f4e8 56%,#e5e9d9);color:#35422d;box-shadow:0 34px 72px rgba(65,76,44,.14),inset 0 1px #fff}
    .exhibition-header::before{border-color:rgba(85,97,58,.13);box-shadow:0 0 0 18px rgba(85,97,58,.025),0 0 0 58px rgba(85,97,58,.02),0 0 0 110px rgba(85,97,58,.015)}
    .exhibition-eyebrow{color:#717a5c}.exhibition-eyebrow span{background:#ab925b;box-shadow:0 0 12px rgba(171,146,91,.4)}
    .exhibition-header h1{color:#35422d;text-shadow:0 2px 18px rgba(255,255,255,.45)}
    .exhibition-subtitle{color:#5d6649}.exhibition-theme{color:#7b8257}
    .exhibition-description{color:#535a47}
    .exhibition-hero-dates{border-color:#a98d52}.exhibition-hero-dates span{color:#7c806d}.exhibition-hero-dates strong{color:#35422d}
    .exhibition-poster-stage::before{border-color:rgba(85,97,58,.16);box-shadow:0 0 50px rgba(198,166,107,.13)}
    .exhibition-poster-shadow{border-color:rgba(169,141,82,.5);background:linear-gradient(145deg,rgba(255,255,255,.8),rgba(222,215,190,.44));box-shadow:0 36px 55px rgba(65,76,44,.17),inset 0 0 0 8px rgba(255,255,255,.24)}
    .exhibition-poster-card{border-color:#fff;border-radius:10px;background:linear-gradient(145deg,#fffef8,#e8e1cf);box-shadow:0 36px 60px rgba(65,76,44,.23),0 0 0 1px rgba(169,141,82,.38),inset 0 0 0 5px rgba(255,255,255,.72)}
    .exhibition-poster-card::after{border-color:rgba(49,52,39,.14)}
    .exhibition-poster-fallback{background:radial-gradient(circle at 80% 18%,rgba(255,255,255,.58),transparent 36%),linear-gradient(145deg,#a4a984,#68794f);color:#fffdf3}
    .exhibition-poster-fallback span,.exhibition-poster-fallback small{color:rgba(255,253,243,.8)}
    .exhibition-header:hover .exhibition-poster-card{box-shadow:0 45px 70px rgba(65,76,44,.28),0 0 0 1px rgba(169,141,82,.5),inset 0 0 0 5px rgba(255,255,255,.72)}
    .exhibition-poster-caption{color:#747b5d}
    .exhibition-facts>div{background:linear-gradient(145deg,#fffef9,#f3f1e7);box-shadow:0 9px 24px rgba(65,76,44,.07)}
    .exhibition-visit-status,.exhibition-apply,.exhibition-artist{border-color:#e4e1d4;border-radius:16px;background:linear-gradient(145deg,#fffef9,#f5f3e8);box-shadow:0 10px 26px rgba(65,76,44,.06)}
    @media(max-width:760px){.exhibition-header{background:radial-gradient(ellipse at 70% 90%,rgba(215,199,151,.52),transparent 42%),linear-gradient(140deg,#edf0e2,#f7f4e8)}}

    /* A light, dimensional gallery for the artists' submitted work. */
    .exhibition-artists{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));align-items:start;gap:26px;margin-top:76px}
    .exhibition-gallery-heading{grid-column:1/-1;max-width:780px;margin-bottom:6px}
    .exhibition-gallery-heading>span{color:#8b8b67;font-size:.7rem;font-weight:800;letter-spacing:.2em}
    .exhibition-gallery-heading h2{margin:12px 0 10px;color:#3f4c33;font:400 clamp(2.2rem,4.5vw,3.6rem)/1.08 var(--serif);letter-spacing:-.035em}
    .exhibition-gallery-heading p{max-width:740px;margin:0;color:#6e755c;line-height:1.75}
    .exhibition-artwork-card{min-width:0;margin:0;padding:0;overflow:hidden;border:1px solid #e1dfcf;border-radius:22px;background:linear-gradient(155deg,#fffef9,#f5f2e7);box-shadow:0 18px 40px rgba(71,79,50,.09),inset 0 1px #fff;transform-style:preserve-3d;transition:transform .4s cubic-bezier(.2,.75,.25,1),box-shadow .4s,border-color .3s}
    .exhibition-artwork-card:hover{transform:perspective(1100px) translateY(-8px) rotateX(1.5deg) rotateY(-1deg);border-color:#c8bc91;box-shadow:0 30px 56px rgba(71,79,50,.15),inset 0 1px #fff}
    .exhibition-artwork-frame{position:relative;display:grid;min-height:300px;aspect-ratio:1/1.06;place-items:center;overflow:hidden;padding:20px;background:radial-gradient(ellipse at 50% 38%,#fffef9 0%,#f1eedf 57%,#e6e8d9 100%);isolation:isolate}
    .exhibition-artwork-frame::before{position:absolute;z-index:-1;inset:8% 13%;border:1px solid rgba(103,119,76,.17);border-radius:50%;content:"";transform:rotate(-18deg) scaleY(.43)}
    .exhibition-artwork-frame::after{position:absolute;inset:12px;border:1px solid rgba(116,124,87,.17);border-radius:14px;content:"";pointer-events:none}
    .exhibition-submitted-image{display:block;width:100%;height:100%;min-height:260px;max-height:none;padding:9px;object-fit:contain;background:linear-gradient(145deg,#fffefa,#f8f5e9);border:1px solid rgba(255,255,255,.95);box-shadow:0 20px 35px rgba(68,76,50,.15);transform:translateZ(22px) rotateY(-2deg);transition:transform .55s cubic-bezier(.2,.75,.25,1),box-shadow .55s}
    .exhibition-artwork-card:hover .exhibition-submitted-image{transform:translate3d(0,-5px,38px) rotateY(0) scale(1.015);box-shadow:0 28px 42px rgba(68,76,50,.2)}
    .exhibition-artwork-empty{display:grid;width:86%;min-height:245px;align-content:center;gap:14px;padding:24px;border:1px solid rgba(255,255,255,.8);background:linear-gradient(145deg,#b5b995,#78855f);color:#fffdf3;text-align:center;box-shadow:0 20px 36px rgba(68,76,50,.16);transform:rotateY(-4deg) rotateZ(1deg)}
    .exhibition-artwork-empty span{font-size:.6rem;letter-spacing:.18em}
    .exhibition-artwork-empty strong{font:400 1.8rem/1.15 var(--serif)}
    .exhibition-image-unavailable{display:grid;width:min(100%,560px);min-height:300px;align-content:center;justify-items:center;gap:12px;padding:28px;border:1px solid rgba(255,255,255,.9);background:linear-gradient(145deg,#fffef9,#e5e9d9);color:#536143;text-align:center;box-shadow:0 25px 46px rgba(68,76,50,.13)}
    .exhibition-image-unavailable span{font-size:.65rem;font-weight:800;letter-spacing:.18em}.exhibition-image-unavailable strong{font:400 clamp(1.6rem,4vw,2.7rem)/1.1 var(--serif)}.exhibition-image-unavailable small{color:#727763}
    .exhibition-vote-count,.exhibition-place-badge{position:absolute;z-index:2;display:inline-flex;align-items:center;min-height:32px;padding:7px 11px;border:1px solid rgba(128,131,99,.23);border-radius:999px;background:rgba(255,254,248,.92);color:#526142;font-size:.68rem;font-weight:800;letter-spacing:.04em;box-shadow:0 7px 18px rgba(65,76,44,.09);backdrop-filter:blur(8px)}
    .exhibition-vote-count{right:22px;bottom:21px}
    .exhibition-place-badge{top:22px;left:22px}
    .exhibition-place-badge.place-1{border-color:#ddcc9f;background:#f5ecd2;color:#8b6d36}
    .exhibition-place-badge.place-2{border-color:#cbd2b8;background:#edf0e3;color:#536747}
    .exhibition-place-badge.place-3{border-color:#ddc3ae;background:#f4e9df;color:#8a674f}
    .exhibition-artwork-copy{padding:20px 21px 22px}
    .exhibition-artist-identity{display:flex;align-items:center;gap:12px;margin:0 0 14px}
    .exhibition-artist-identity>img,.exhibition-artist-avatar{display:grid;width:48px;height:48px;flex:0 0 48px;place-items:center;overflow:hidden;border:2px solid rgba(255,255,255,.9);border-radius:50%;background:linear-gradient(145deg,#d9d7bb,#8b9872);color:#fffdf3;object-fit:cover;box-shadow:0 7px 18px rgba(65,76,44,.14)}
    .exhibition-artist-identity .exhibition-artist-label{display:block}
    .exhibition-artist-label{color:#8b8b67;font-size:.62rem;font-weight:800;letter-spacing:.16em}
    .exhibition-artwork-copy h3{margin:4px 0 0;color:#75805c;font-size:.86rem;font-weight:700}
    .exhibition-artwork-copy h4{margin:0;color:#3f4c33;font:400 1.55rem/1.16 var(--serif)}
    .exhibition-artwork-format{align-self:flex-start;margin-top:9px;padding:6px 9px;border:1px solid #dfe2d3;border-radius:99px;background:#f4f5ed;color:#677451;font-size:.62rem;font-weight:800;letter-spacing:.11em}
    .exhibition-artwork-description{display:-webkit-box;min-height:4.5em;margin:12px 0;overflow:hidden;color:#727763;line-height:1.55;-webkit-box-orient:vertical;-webkit-line-clamp:3}
    .exhibition-artwork-copy .exhibition-artwork-price{display:flex;justify-content:space-between;gap:8px;margin:16px 0;padding-top:12px;border-top:1px solid #e8e5d8;color:#82856f;font-size:.76rem}
    .exhibition-artwork-price strong{color:#5d6d43}
    .exhibition-result{display:grid;grid-template-columns:1fr auto;align-items:center;gap:4px 12px;border:1px solid #e3ddc9;background:linear-gradient(120deg,#f6f1e1,#f2f3e8);color:#536143}
    .exhibition-result>span{font-size:.74rem;font-weight:700}.exhibition-result>strong{grid-column:2;grid-row:1/3;color:#586a42;font-size:1.7rem}.exhibition-result>small{color:#85866f;font-size:.66rem}
    .exhibition-art-actions{gap:9px}
    .exhibition-art-actions .exhibition-button{padding:11px 14px;border-radius:10px;background:linear-gradient(115deg,#788456,#596b43);box-shadow:0 7px 16px rgba(85,97,58,.15)}
    .exhibition-art-actions .exhibition-button:hover{background:linear-gradient(115deg,#697849,#4d603b);transform:translateY(-1px)}
    .exhibition-art-actions form{display:inline-flex}
    .exhibition-voted,.exhibition-vote-note{padding:10px 12px;border-radius:10px;background:#edf0e4;color:#5e6e48;font-size:.76rem;font-weight:700}
    .exhibition-buy-login{display:inline-block;margin-top:10px;color:#65744c;font-size:.76rem;font-weight:700;text-decoration:underline;text-underline-offset:3px}
    .exhibition-gallery-empty{grid-column:1/-1;padding:32px;border:1px dashed #c6c9b3;border-radius:18px;background:#f8f7ef;color:#70775c}
    .exhibition-gallery-locked{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(220px,.65fr);align-items:center;gap:clamp(24px,6vw,72px);margin:76px 0 22px;padding:clamp(30px,6vw,72px);overflow:hidden;border:1px solid rgba(105,119,74,.2);border-radius:28px;background:radial-gradient(ellipse at 86% 15%,rgba(215,199,151,.56),transparent 38%),linear-gradient(135deg,#f7f5e9,#e9eddd);box-shadow:0 25px 65px rgba(65,76,44,.1)}
    .exhibition-gallery-locked-copy>span,.exhibition-gallery-opening-card>span{color:#898c69;font-size:.68rem;font-weight:800;letter-spacing:.2em}
    .exhibition-gallery-locked h2{max-width:700px;margin:15px 0;color:#3f4c33;font:400 clamp(2rem,4.5vw,3.6rem)/1.08 var(--serif);letter-spacing:-.025em}
    .exhibition-gallery-locked p{max-width:680px;margin:0;color:#69715a;font-size:1rem;line-height:1.8}
    .exhibition-gallery-opening-card{display:grid;min-height:210px;align-content:center;justify-items:center;gap:8px;padding:28px;border:1px solid rgba(255,255,255,.9);border-radius:24px;background:rgba(255,255,250,.65);text-align:center;box-shadow:0 18px 48px rgba(65,76,44,.1);backdrop-filter:blur(14px);transform:perspective(900px) rotateY(-7deg);animation:artwork-float 7s ease-in-out infinite}
    .exhibition-gallery-opening-card strong{color:#46563a;font:400 clamp(2.5rem,5vw,4.2rem)/1 var(--serif)}
    .exhibition-gallery-opening-card small{color:#7b8066;font-size:.8rem;letter-spacing:.12em}
    /* Full-width museum panels keep the artwork and its story together for every artist. */
    .exhibition-artists{grid-template-columns:minmax(0,1fr);gap:30px}
    .exhibition-artwork-card{display:grid;grid-template-columns:minmax(300px,.92fr) minmax(0,1.08fr);grid-template-areas:"artwork copy";align-items:stretch;width:100%;min-height:540px;border-radius:28px;background:linear-gradient(125deg,rgba(255,255,250,.98),rgba(242,241,225,.92));box-shadow:0 26px 70px rgba(71,79,50,.12),inset 0 1px #fff}
    .exhibition-artwork-card:hover{transform:perspective(1400px) translateY(-5px) rotateX(.5deg);box-shadow:0 36px 84px rgba(71,79,50,.18),inset 0 1px #fff}
    .exhibition-artwork-card .exhibition-artwork-frame{grid-area:artwork;display:flex;height:540px;min-height:0;aspect-ratio:auto;align-items:center;justify-content:center;padding:clamp(24px,4vw,54px);background:radial-gradient(ellipse at 50% 42%,#fffefb 0%,#f0eedf 58%,#e2e7d6 100%)}
    .exhibition-artwork-card .exhibition-artwork-frame::before{inset:8% 12%;border-color:rgba(103,119,76,.22);transform:rotate(-18deg) scaleY(.48);animation:artwork-orbit 16s ease-in-out infinite}
    .exhibition-artwork-card .exhibition-submitted-image{width:auto;height:auto;max-width:100%;max-height:100%;min-height:0;padding:12px;object-fit:contain;box-shadow:0 30px 55px rgba(68,76,50,.2);transform:perspective(1100px) translateZ(25px) rotateY(-3deg);animation:artwork-float 7s ease-in-out infinite}
    .exhibition-artwork-card:hover .exhibition-submitted-image{transform:perspective(1100px) translate3d(0,-4px,38px) rotateY(0) scale(1.02);box-shadow:0 38px 68px rgba(68,76,50,.25);animation-play-state:paused}
    .exhibition-artwork-card .exhibition-artwork-copy{grid-area:copy;display:flex;flex-direction:column;justify-content:center;padding:clamp(28px,4.6vw,62px);border-left:1px solid rgba(117,124,87,.16);background:linear-gradient(145deg,rgba(255,255,250,.78),rgba(245,243,231,.88));backdrop-filter:blur(16px);transform:translateZ(8px);transition:background .35s,transform .45s}
    .exhibition-artwork-card:hover .exhibition-artwork-copy{background:linear-gradient(145deg,rgba(255,255,250,.96),rgba(239,239,220,.94));transform:translate3d(0,-2px,16px)}
    .exhibition-artwork-card .exhibition-artist-identity{margin-bottom:24px}
    .exhibition-artwork-card .exhibition-artist-identity>img,.exhibition-artwork-card .exhibition-artist-avatar{width:58px;height:58px;flex-basis:58px}
    .exhibition-artwork-card .exhibition-artwork-copy h3{font-size:1rem}
    .exhibition-artwork-card .exhibition-artwork-copy h4{font-size:clamp(2rem,3.6vw,3.35rem);line-height:1.04}
    .exhibition-artwork-card .exhibition-artwork-description{position:relative;display:block;min-height:0;margin:18px 0;padding-left:16px;border-left:2px solid rgba(169,141,82,.5);color:#666e57;font-size:1.02rem;line-height:1.8;transition:transform .4s,color .3s}
    .exhibition-artwork-card:hover .exhibition-artwork-description{color:#4f5d3c;transform:translate3d(3px,0,8px)}
    .exhibition-artwork-card .exhibition-artwork-price{margin-top:12px;padding-top:18px;font-size:.9rem}
    .exhibition-artwork-card .exhibition-art-actions{margin-top:10px}
    .exhibition-vote-widget{position:absolute;z-index:4;top:22px;right:22px;display:flex;align-items:center;gap:9px}
    .exhibition-vote-widget form{margin:0}
    .exhibition-artwork-card .exhibition-vote-count{position:static;min-width:42px;justify-content:center;background:rgba(255,254,248,.84)}
    .exhibition-heart{display:grid;width:48px;height:48px;place-items:center;border:1px solid rgba(255,255,255,.94);border-radius:50%;background:rgba(255,254,248,.86);color:#8b685a;font-size:1.55rem;line-height:1;text-decoration:none;box-shadow:0 12px 28px rgba(65,76,44,.16);backdrop-filter:blur(14px);cursor:pointer;transition:transform .25s,background .25s,color .25s,box-shadow .25s}
    .exhibition-heart:hover{transform:translateY(-3px) scale(1.09);background:#fff;color:#b75d55;box-shadow:0 16px 32px rgba(146,74,66,.22)}
    .exhibition-heart.is-voted{background:#fff0ec;color:#c45854;cursor:default;animation:heart-pop .4s ease-out}
    .exhibition-heart.is-disabled{color:#9b9a88;cursor:not-allowed}
    .exhibition-image-unavailable{height:100%;min-height:0}
    .exhibition-image-unavailable[hidden]{display:none!important}
    @keyframes artwork-float{0%,100%{transform:perspective(1100px) translate3d(0,0,20px) rotateY(-3deg)}50%{transform:perspective(1100px) translate3d(0,-9px,34px) rotateY(-1deg)}}
    @keyframes artwork-orbit{0%,100%{transform:rotate(-18deg) scaleY(.48) translateY(0)}50%{transform:rotate(-14deg) scaleY(.48) translateY(-8px)}}
    @keyframes heart-pop{0%{transform:scale(.7)}70%{transform:scale(1.16)}100%{transform:scale(1)}}
    @media(max-width:940px){.exhibition-artists{gap:22px}.exhibition-artwork-card{grid-template-columns:minmax(260px,.88fr) minmax(0,1.12fr)}}
    @media(max-width:700px){.exhibition-artwork-card{grid-template-columns:1fr;grid-template-areas:"artwork" "copy";min-height:0}.exhibition-artwork-card .exhibition-artwork-frame{height:min(68vh,620px);min-height:390px;padding:24px}.exhibition-artwork-card .exhibition-submitted-image{max-width:100%;max-height:100%;min-height:0}.exhibition-artwork-card .exhibition-artwork-copy{padding:28px;border-left:0;border-top:1px solid rgba(117,124,87,.16)}}
    @media(max-width:700px){.exhibition-gallery-locked{grid-template-columns:1fr;margin-top:56px}.exhibition-gallery-opening-card{min-height:170px;transform:none}}
    @media(max-width:560px){.exhibition-artists{grid-template-columns:1fr;gap:18px;margin-top:56px}.exhibition-gallery-heading{margin-bottom:4px}.exhibition-artwork-frame{min-height:260px;aspect-ratio:1/1}.exhibition-submitted-image{min-height:220px}.exhibition-artwork-copy{padding:18px}.exhibition-artwork-description{min-height:0}}
    @media(prefers-reduced-motion:reduce){.exhibition-artwork-card,.exhibition-submitted-image,.exhibition-art-actions .exhibition-button{transition:none;animation:none!important}}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('exhibition-artwork-type');
    const digitalFileField = document.getElementById('exhibition-digital-file-field');
    const digitalFileInput = digitalFileField?.querySelector('input[type="file"]');
    if (!typeSelect || !digitalFileField || !digitalFileInput) return;

    function updateDigitalField() {
        const digital = typeSelect.value === 'digital';
        digitalFileField.hidden = !digital;
        digitalFileInput.required = digital
            && !digitalFileField.querySelector('span');
    }

    typeSelect.addEventListener('change', updateDigitalField);
    updateDigitalField();
});
</script>
@endpush
