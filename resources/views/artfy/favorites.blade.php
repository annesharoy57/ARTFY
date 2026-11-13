@extends('artfy.layout')

@section('title', 'My Favorites | Artfy')

@section('content')
@php
    $resolveFavoriteImage = function ($image) {
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
@endphp

<main class="favorites-page">
    <section class="favorites-hero">
        <div class="favorites-hero-copy">
            <a href="{{ route('artworks.index') }}" class="favorites-back">&larr; Explore artworks</a>
            <span class="favorites-kicker">YOUR PERSONAL COLLECTION</span>
            <h1>Art worth<br><em>keeping close.</em></h1>
            <p>A quiet corner for the pieces that caught your eye. Revisit them whenever inspiration calls.</p>
        </div>

        <div class="favorites-hero-art" aria-hidden="true">
            <div class="favorites-orbit orbit-one"></div>
            <div class="favorites-orbit orbit-two"></div>
            <div class="favorites-heart-mark">&#9825;</div>
            <span class="favorites-art-caption">A collection of your own</span>
        </div>

        <div class="favorites-count">
            <span class="favorites-count-number">{{ str_pad((string) count($items), 2, '0', STR_PAD_LEFT) }}</span>
            <span>{{ count($items) === 1 ? 'saved artwork' : 'saved artworks' }}</span>
        </div>
    </section>

    <section class="favorites-collection" aria-labelledby="favorites-title">
        <header class="favorites-collection-heading">
            <div>
                <span class="favorites-section-kicker">THE PIECES YOU SAVED</span>
                <h2 id="favorites-title">Your collection</h2>
            </div>
            @if (count($items) > 0)
                <span class="favorites-total-label">{{ count($items) }} {{ count($items) === 1 ? 'piece' : 'pieces' }}</span>
            @endif
        </header>

        @if (count($items) > 0)
            <div class="favorites-grid">
                @foreach ($items as $item)
                    @php
                        $favoriteImage = $resolveFavoriteImage($item['image'] ?? null);
                        $isSold = !empty($item['is_sold']) || (($item['status'] ?? '') === 'sold');
                    @endphp

                    <article class="favorite-art-card">
                        <a class="favorite-art-link" href="{{ route('artworks.show', $item['slug']) }}">
                            <div class="favorite-art-image">
                                @if ($favoriteImage)
                                    <img src="{{ $favoriteImage }}" alt="{{ $item['title'] ?? 'Saved artwork' }}" loading="lazy" onerror="this.hidden=true; this.nextElementSibling.hidden=false;">
                                    <span class="favorite-image-fallback" hidden>Artwork image unavailable</span>
                                @else
                                    <span class="favorite-image-fallback">Artwork image unavailable</span>
                                @endif
                                <span class="favorite-art-category">{{ $item['category'] ?? 'Original artwork' }}</span>
                            </div>

                            <div class="favorite-art-info">
                                <div class="favorite-art-heading">
                                    <h3>{{ $item['title'] ?? 'Untitled artwork' }}</h3>
                                    <span class="favorite-view-mark" aria-hidden="true">&#8599;</span>
                                </div>
                                <p>By {{ $item['artist'] ?? 'Artfy Artist' }}</p>
                                <div class="favorite-art-footer">
                                    <span class="favorite-art-price">
                                        @if (isset($item['price']) && $item['price'] !== null)
                                            BDT {{ number_format((float) $item['price'], 2) }}
                                        @else
                                            Price not listed
                                        @endif
                                    </span>
                                    <span class="favorite-status {{ $isSold ? 'is-sold' : '' }}">{{ $isSold ? 'Collected' : 'Available' }}</span>
                                </div>
                            </div>
                        </a>

                        <form class="favorite-remove-form" action="{{ route('artworks.favorite', $item['id']) }}" method="POST">
                            @csrf
                            <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                            <button type="submit" class="favorite-remove" aria-label="Remove {{ $item['title'] ?? 'artwork' }} from favorites" title="Remove from favorites">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4.5A2.5 2.5 0 0 1 8.5 2h7A2.5 2.5 0 0 1 18 4.5V22l-6-4-6 4V4.5Z"/></svg>
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        @else
            <div class="favorites-empty">
                <div class="favorites-empty-art" aria-hidden="true">
                    <span>&#9825;</span>
                    <i></i><i></i>
                </div>
                <span class="favorites-section-kicker">A COLLECTION IN THE MAKING</span>
                <h3>Save the art that speaks to you.</h3>
                <p>When you find a piece you love, save it here and it will be waiting in your personal collection.</p>
                <a href="{{ route('artworks.index') }}" class="favorites-discover-button">Discover artworks <span aria-hidden="true">&rarr;</span></a>
            </div>
        @endif
    </section>
</main>

<style>
    .favorites-page {
        --fav-olive: #465333;
        --fav-moss: #76815b;
        --fav-paper: #fffdf7;
        --fav-line: #e3e1d3;
        max-width: 1380px;
        margin: 0 auto;
        padding: 44px 28px 82px;
        color: #333a2b;
    }

    .favorites-page *, .favorites-page *::before, .favorites-page *::after { box-sizing: border-box; }
    .favorites-hero { position: relative; display: grid; grid-template-columns: minmax(0, 1fr) minmax(280px, .8fr); align-items: center; gap: 24px; min-height: 370px; overflow: hidden; padding: clamp(30px, 5vw, 58px); border: 1px solid #dedccb; border-radius: 26px; background: radial-gradient(ellipse at 80% 0%, rgba(206, 185, 135, .27), transparent 34%), linear-gradient(125deg, #f7f5e9, #e8eadc 72%, #f3efe2); box-shadow: 0 24px 58px rgba(63, 73, 45, .09); }
    .favorites-hero-copy { position: relative; z-index: 2; max-width: 550px; }
    .favorites-back { display: inline-flex; margin-bottom: 30px; color: #697653; font-size: 12px; font-weight: 700; text-decoration: none; }
    .favorites-back:hover { color: #36432b; text-decoration: underline; text-underline-offset: 4px; }
    .favorites-kicker, .favorites-section-kicker { display: block; color: #8a8d71; font-size: 10px; font-weight: 800; letter-spacing: .18em; }
    .favorites-hero h1 { margin: 13px 0 15px; color: #38442d; font-family: 'Playfair Display', Georgia, serif; font-size: clamp(44px, 6vw, 76px); font-weight: 500; letter-spacing: -.045em; line-height: .99; }
    .favorites-hero h1 em { color: #a58b59; font-weight: 400; }
    .favorites-hero-copy > p { max-width: 440px; margin: 0; color: #6f755f; font-size: 14px; line-height: 1.8; }

    .favorites-hero-art { position: relative; display: grid; min-height: 245px; place-items: center; }
    .favorites-orbit { position: absolute; width: min(27vw, 300px); aspect-ratio: 1; border: 1px solid rgba(97, 110, 72, .2); border-radius: 50%; }
    .orbit-one { transform: rotate(-24deg) scaleY(.74); }
    .orbit-two { transform: rotate(32deg) scaleY(.74); }
    .favorites-heart-mark { position: relative; display: grid; width: 148px; height: 174px; place-items: center; border: 1px solid rgba(93, 105, 69, .2); border-radius: 52% 48% 42% 58% / 42% 44% 56% 58%; background: linear-gradient(145deg, rgba(255, 255, 249, .82), rgba(215, 221, 199, .7)); box-shadow: 0 25px 45px rgba(65, 76, 44, .15), inset 0 1px 0 rgba(255,255,255,.9); color: #75815b; font-family: Georgia, serif; font-size: 86px; transform: rotate(-7deg); }
    .favorites-heart-mark::after { position: absolute; inset: 11px; border: 1px solid rgba(105, 116, 79, .17); border-radius: inherit; content: ''; }
    .favorites-art-caption { position: absolute; right: 3%; bottom: 7%; color: #81866c; font-size: 9px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; }
    .favorites-count { position: absolute; right: 25px; bottom: 22px; display: flex; align-items: baseline; gap: 8px; color: #71775d; font-size: 11px; }
    .favorites-count-number { color: #4c5a38; font-family: Georgia, serif; font-size: 18px; }

    .favorites-collection { padding-top: 42px; }
    .favorites-collection-heading { display: flex; align-items: end; justify-content: space-between; gap: 15px; margin-bottom: 20px; }
    .favorites-collection-heading h2 { margin: 7px 0 0; color: #414c2f; font-family: 'Playfair Display', Georgia, serif; font-size: 31px; font-weight: 500; }
    .favorites-total-label { padding: 8px 13px; border: 1px solid #e1e2d4; border-radius: 99px; background: #f4f4eb; color: #697452; font-size: 11px; font-weight: 700; }
    .favorites-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 23px; }
    .favorite-art-card { position: relative; min-width: 0; overflow: hidden; border: 1px solid #e4e1d4; border-radius: 19px; background: var(--fav-paper); box-shadow: 0 11px 28px rgba(61, 70, 46, .07); transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
    .favorite-art-card:hover { transform: translateY(-6px); border-color: #c6cbb6; box-shadow: 0 22px 42px rgba(61, 70, 46, .14); }
    .favorite-art-link { display: block; color: inherit; text-decoration: none; }
    .favorite-art-image { position: relative; display: grid; height: 285px; place-items: center; overflow: hidden; padding: 13px; background: radial-gradient(ellipse at 50% 30%, #fbf8ed, #e8eadf); }
    .favorite-art-image::after { position: absolute; inset: 12px; border: 1px solid rgba(95, 106, 73, .18); content: ''; pointer-events: none; }
    .favorite-art-image img { width: 100%; height: 100%; object-fit: cover; transition: transform .45s ease; }
    .favorite-art-card:hover .favorite-art-image img { transform: scale(1.045); }
    .favorite-art-category { position: absolute; z-index: 1; left: 22px; bottom: 22px; max-width: calc(100% - 44px); padding: 7px 10px; border: 1px solid rgba(255,255,255,.55); border-radius: 99px; background: rgba(255, 253, 247, .88); color: #4e5b3b; font-size: 9px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; backdrop-filter: blur(8px); }
    .favorite-image-fallback { color: #81876e; font-family: Georgia, serif; font-size: 14px; }
    .favorite-image-fallback[hidden] { display: none; }
    .favorite-art-info { padding: 18px 19px 20px; }
    .favorite-art-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
    .favorite-art-heading h3 { margin: 0; color: #35422d; font-family: 'Playfair Display', Georgia, serif; font-size: 22px; font-weight: 500; line-height: 1.25; }
    .favorite-view-mark { display: grid; width: 29px; height: 29px; flex: 0 0 29px; place-items: center; border: 1px solid #e3e3d7; border-radius: 50%; color: #61704a; font-size: 16px; }
    .favorite-art-info > p { margin: 7px 0 17px; color: #7a806c; font-size: 12px; }
    .favorite-art-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-top: 13px; border-top: 1px solid #eeece2; }
    .favorite-art-price { color: #4c5a38; font-size: 12px; font-weight: 800; }
    .favorite-status { color: #697b52; font-size: 10px; font-weight: 700; }
    .favorite-status::before { display: inline-block; width: 6px; height: 6px; margin: 0 6px 1px 0; border-radius: 50%; background: #7d9663; content: ''; }
    .favorite-status.is-sold { color: #91805d; }
    .favorite-status.is-sold::before { background: #b49b69; }
    .favorite-remove-form { position: absolute; z-index: 2; top: 22px; right: 22px; }
    .favorite-remove { display: grid; width: 39px; height: 39px; place-items: center; border: 1px solid rgba(255,255,255,.7); border-radius: 50%; background: rgba(255, 253, 247, .94); color: #4c5a38; cursor: pointer; box-shadow: 0 5px 14px rgba(42, 50, 34, .15); transition: transform .18s ease, background .18s ease, color .18s ease; }
    .favorite-remove:hover { transform: scale(1.08); background: #4c5a38; color: #fff; }
    .favorite-remove svg { width: 18px; height: 18px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }

    .favorites-empty { display: flex; min-height: 360px; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; border: 1px dashed #d8dacb; border-radius: 22px; background: linear-gradient(145deg, #fffefa, #f0f1e7); text-align: center; }
    .favorites-empty-art { position: relative; display: grid; width: 92px; height: 92px; place-items: center; margin-bottom: 22px; border: 1px solid #dfe2d3; border-radius: 28px; background: #e9ecdf; color: #7a8661; }
    .favorites-empty-art span { z-index: 1; font-family: Georgia, serif; font-size: 49px; }
    .favorites-empty-art i { position: absolute; width: 67px; height: 67px; border: 1px solid rgba(85,97,58,.18); border-radius: 50%; }
    .favorites-empty-art i:last-child { transform: rotate(60deg) scaleX(.7); }
    .favorites-empty h3 { margin: 10px 0 8px; color: #414c2f; font-family: 'Playfair Display', Georgia, serif; font-size: 29px; font-weight: 500; }
    .favorites-empty p { max-width: 440px; margin: 0; color: #757b66; font-size: 13px; line-height: 1.8; }
    .favorites-discover-button { display: inline-flex; align-items: center; gap: 12px; margin-top: 21px; padding: 12px 17px; border-radius: 99px; background: #4c5a38; color: #fff; font-size: 12px; font-weight: 700; text-decoration: none; transition: background .18s ease, transform .18s ease; }
    .favorites-discover-button:hover { transform: translateY(-2px); background: #35432b; color: #fff; }

    @media (max-width: 980px) { .favorites-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 17px; } .favorite-art-image { height: 245px; } }
    @media (max-width: 680px) { .favorites-page { padding: 25px 16px 55px; } .favorites-hero { grid-template-columns: 1fr; min-height: auto; padding: 29px 24px 52px; } .favorites-back { margin-bottom: 23px; } .favorites-hero-art { min-height: 190px; } .favorites-orbit { width: 210px; } .favorites-heart-mark { width: 115px; height: 135px; font-size: 66px; } .favorites-count { right: 18px; bottom: 15px; } .favorites-collection { padding-top: 32px; } .favorites-grid { grid-template-columns: 1fr; } .favorite-art-image { height: min(78vw, 380px); } }
    @media (prefers-reduced-motion: reduce) { .favorites-page *, .favorites-page *::before, .favorites-page *::after { transition: none !important; animation: none !important; } }
</style>
@endsection
