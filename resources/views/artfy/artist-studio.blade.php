
@extends('artfy.layout')

@section('title', 'Artist Studio')

@section('content')
<div class="studio-page">
    <header class="studio-heading">
        <p class="studio-eyebrow">YOUR CREATIVE SPACE</p>
        <h1>Artist Studio</h1>
        <p>Manage your collection, share new work, and keep track of sold pieces.</p>
        <a class="studio-button" href="{{ route('artist.artworks.create') }}">
            + Upload artwork
        </a>
        <a class="studio-text-link" href="{{ route('artist-profile.edit') }}">
            Edit artist profile →
        </a>
    </header>

    @if (session('success'))
        <div class="studio-message">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="studio-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @php
        $availableArtworks = $artworks->where('is_sold', false);
        $soldArtworks = $artworks->where('is_sold', true);
    @endphp

    <section class="studio-section">
        <div class="studio-section-heading">
            <h2>Available artworks</h2>
            <span>{{ $availableArtworks->count() }} pieces</span>
        </div>

        @if ($availableArtworks->isEmpty())
            <div class="studio-empty">
                <h3>Your collection begins here.</h3>
                <p>You haven't uploaded any available artworks yet.</p>
                <a href="{{ route('artist.artworks.create') }}">Upload your first artwork →</a>
            </div>
        @else
            <div class="studio-grid">
                @foreach ($availableArtworks as $artwork)
                    <article class="studio-artwork">
                        <img
                            src="{{ asset('storage/' . $artwork->image) }}"
                            alt="{{ $artwork->title }}"
                        >
                        <div class="studio-artwork-info">
                            <h3>{{ $artwork->title }}</h3>
                            <p>{{ $artwork->category ?: 'Original artwork' }}</p>

                            @if ($artwork->price !== null)
                                <strong>৳{{ number_format((float) $artwork->price, 2) }}</strong>
                            @else
                                <strong>Price not set</strong>
                            @endif

                            <form
                                action="{{ route('artist.artworks.destroy', $artwork) }}"
                                method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this artwork?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button class="studio-delete" type="submit">
                                    Delete artwork
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="studio-section">
        <div class="studio-section-heading">
            <h2>Sold artworks</h2>
            <span>{{ $soldArtworks->count() }} pieces</span>
        </div>

        @if ($soldArtworks->isEmpty())
            <div class="studio-empty">
                <p>Your sold artworks will appear here when marked as sold.</p>
            </div>
        @else
            <div class="studio-grid">
                @foreach ($soldArtworks as $artwork)
                    <article class="studio-artwork sold-artwork">
                        <img
                            src="{{ asset('storage/' . $artwork->image) }}"
                            alt="{{ $artwork->title }}"
                        >
                        <div class="studio-artwork-info">
                            <h3>{{ $artwork->title }}</h3>
                            <span class="sold-label">SOLD</span>
                            @if ($artwork->price !== null)
                                <strong>৳{{ number_format((float) $artwork->price, 2) }}</strong>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>

<style>
    .studio-page {
        max-width: 1180px;
        margin: auto;
        padding: 48px 24px;
        color: #414c2c;
    }

    .studio-heading { margin-bottom: 42px; }
    .studio-eyebrow {
        color: #72795f;
        letter-spacing: 3px;
        font-size: 12px;
        font-weight: 700;
    }
    .studio-heading h1 { font-size: clamp(32px, 5vw, 48px); margin: 12px 0; }
    .studio-heading > p:not(.studio-eyebrow) { color: #72795f; line-height: 1.7; }

    .studio-button {
        display: inline-block;
        background: #55613a;
        color: white;
        text-decoration: none;
        padding: 13px 20px;
        border-radius: 8px;
        margin: 14px 16px 0 0;
    }
    .studio-button:hover { background: #414c2c; }
    .studio-text-link { color: #414c2c; font-weight: 600; }

    .studio-section { margin: 38px 0; }
    .studio-section-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    .studio-section-heading h2 { font-size: 25px; margin: 0; }
    .studio-section-heading span { color: #72795f; }

    .studio-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 22px;
    }
    .studio-artwork {
        overflow: hidden;
        background: #fffef9;
        border: 1px solid #e1e2d5;
        border-radius: 14px;
    }
    .studio-artwork > img {
        width: 100%;
        height: 230px;
        display: block;
        object-fit: cover;
    }
    .studio-artwork-info { padding: 17px; }
    .studio-artwork-info h3 { margin: 0 0 8px; }
    .studio-artwork-info p { color: #72795f; }
    .studio-artwork-info strong { display: block; margin: 12px 0; }

    .studio-empty {
        background: #fffef9;
        border: 1px dashed #c8ccb8;
        padding: 28px;
        border-radius: 14px;
    }
    .studio-empty p { color: #72795f; }
    .studio-empty a { color: #414c2c; font-weight: 600; }

    .studio-delete {
        background: transparent;
        color: #8a3025;
        border: 1px solid #d9aaa4;
        border-radius: 7px;
        padding: 9px 12px;
        cursor: pointer;
    }
    .sold-label {
        display: inline-block;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        color: #8a3025;
    }
    .studio-message, .studio-error {
        padding: 14px 18px;
        border-radius: 9px;
        margin: 18px 0;
    }
    .studio-message { background: #e8efe0; }
    .studio-error { background: #f8e8e5; color: #8a3025; }

    @media (max-width: 600px) {
        .studio-page { padding: 28px 16px; }
        .studio-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection
