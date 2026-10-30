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
    <header class="exhibition-header">
        <p class="exhibition-eyebrow">ONLINE EXHIBITION</p>
        <h1>{{ $exhibition->title }}</h1>
        @if ($exhibition->subtitle)
            <p class="exhibition-subtitle">{{ $exhibition->subtitle }}</p>
        @endif
        <p class="exhibition-description">{{ $exhibition->description }}</p>
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

    <section class="exhibition-artists">
        <h2>Participating artists</h2>
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
            <article class="exhibition-artist">
                <h3>{{ $artistName }}</h3>
                @if ($application->artwork_image)
                    <img
                        class="exhibition-submitted-image"
                        src="{{ asset('storage/' . $application->artwork_image) }}"
                        alt="{{ $application->artwork_title }}"
                        loading="lazy"
                    >
                @endif
                <h4>{{ $application->artwork_title }}</h4>
                <p>{{ $application->artwork_description }}</p>
                <p class="exhibition-artwork-price">Asking price: ৳{{ number_format((float) $application->artwork_price, 2) }}</p>
            </article>
        @empty
            <p>Participating artists will appear here after their registration payment is confirmed.</p>
        @endforelse
    </section>
</main>

<style>
    .exhibition-page{width:min(1120px,100%);margin:auto;padding:44px 24px 70px;color:#292d22}
    .exhibition-back{color:#55613a;text-decoration:none;font-weight:600}
    .exhibition-header{max-width:780px;padding:46px 0 30px}
    .exhibition-eyebrow{color:#72795f;font-size:.75rem;font-weight:700;letter-spacing:.16em}
    .exhibition-header h1{margin:12px 0;font:600 clamp(2.2rem,6vw,4.2rem)/1.1 var(--serif)}
    .exhibition-subtitle{font-size:1.2rem;color:#55613a}
    .exhibition-description{white-space:pre-line;line-height:1.8}
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
    .exhibition-artwork-form{display:grid;gap:14px;max-width:680px;margin-top:18px}
    .exhibition-artwork-form label{display:grid;gap:7px;font-weight:600}
    .exhibition-artwork-form input,.exhibition-artwork-form textarea{width:100%;padding:11px;border:1px solid #e5e2d6;border-radius:7px;background:#fff;font:inherit}
    .exhibition-artwork-form input[type=file]{padding:9px}
    .exhibition-edit-image{display:block;width:min(100%,360px);max-height:300px;object-fit:contain;background:#f5f3e8}
    .exhibition-artwork-form textarea{resize:vertical}
    .exhibition-submitted-image{display:block;width:min(100%,560px);max-height:520px;object-fit:contain;background:#f5f3e8}
    .exhibition-artwork-price{font-weight:700;color:#55613a}
    .exhibition-alert{padding:12px 16px;border-radius:8px}
    .exhibition-alert.success{background:#e5f3e5;color:#285b31}
    .exhibition-alert.error{background:#fbe8e6;color:#922f32}
    @media(max-width:700px){.exhibition-facts{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
@endsection
