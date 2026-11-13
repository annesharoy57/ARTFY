@extends('artfy.layout')

@section('title', $title)

@section('content')
@php
    $resolveArtfyImage = function ($image) {
        if (empty($image)) {
            return null;
        }

        $image = trim((string) $image);

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        if (str_starts_with($image, 'storage/')) {
            return asset($image);
        }

        return asset('storage/' . ltrim($image, '/'));
    };

    $featuredArtworks = collect($items)
        ->filter(fn ($item) => !empty($item['image']))
        ->values();
@endphp

<style>
    .art-gallery {
        --cream: #f5f3e8;
        --olive: #55613a;
        --dark: #303a25;
        --muted: #72795f;
        --line: #e4e3d7;
        --paper: #fffef9;
        color: var(--dark);
    }

    .art-gallery *,
    .art-gallery *::before,
    .art-gallery *::after {
        box-sizing: border-box;
    }

    .art-gallery a {
        -webkit-tap-highlight-color: transparent;
    }

    /* SHARED HERO */

    .ag-hero {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 420px;
        overflow: hidden;
        isolation: isolate;
        padding: clamp(28px, 5vw, 65px);
        border-radius: 18px;
        background: var(--dark);
        color: var(--cream);
    }

    .ag-hero-background {
        position: absolute;
        z-index: -3;
        inset: 0;
        overflow: hidden;
        background: var(--dark);
    }

    .ag-background-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        background-position: center;
        background-size: cover;
        transform: scale(1.04);
        transition: opacity 1.2s ease, transform 5s ease;
    }

    .ag-background-slide.is-active {
        opacity: 1;
        transform: scale(1);
    }

    .ag-hero::before {
        content: "";
        position: absolute;
        z-index: -2;
        inset: 0;
        background: linear-gradient(
            90deg,
            rgba(38, 48, 28, .91),
            rgba(48, 60, 35, .78) 43%,
            rgba(65, 78, 45, .48)
        );
        pointer-events: none;
    }

    .ag-hero::after {
        content: "";
        position: absolute;
        z-index: -1;
        inset: 0;
        border: 1px solid rgba(245, 243, 232, .15);
        border-radius: inherit;
        pointer-events: none;
    }

    .ag-hero-copy {
        position: relative;
        z-index: 2;
        width: min(100%, 650px);
    }

    .ag-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        color: #d4ddbd;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .19em;
        text-transform: uppercase;
    }

    .ag-eyebrow::before {
        content: "";
        width: 25px;
        height: 1px;
        background: currentColor;
    }

    .ag-hero h1 {
        margin: 0;
        color: var(--cream);
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(43px, 6vw, 76px);
        font-weight: 400;
        letter-spacing: -.045em;
        line-height: 1.04;
    }

    .ag-hero h1 em {
        color: #d2ddb6;
        font-weight: 400;
    }

    .ag-hero-copy > p {
        max-width: 490px;
        margin: 22px 0 27px;
        color: rgba(255, 254, 249, .88);
        font-size: 14px;
        line-height: 1.9;
    }

    .ag-hero-button {
        display: inline-flex;
        align-items: center;
        gap: 13px;
        padding: 13px 18px;
        border: 1px solid rgba(255, 255, 255, .6);
        border-radius: 5px;
        background: var(--cream);
        color: var(--dark);
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        transition: background .2s, transform .2s;
    }

    .ag-hero-button:hover {
        transform: translateY(-2px);
        background: white;
        color: var(--dark);
    }

    .ag-hero-bottom {
        position: absolute;
        z-index: 3;
        right: 28px;
        bottom: 22px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ag-hero-indicators {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ag-hero-dot {
        width: 7px;
        height: 7px;
        padding: 0;
        border: 0;
        border-radius: 50px;
        background: rgba(255, 255, 255, .5);
        cursor: pointer;
        transition: width .25s, background .25s;
    }

    .ag-hero-dot.is-active {
        width: 23px;
        background: var(--cream);
    }

    .ag-hero-number {
        color: white;
        font-size: 10px;
        letter-spacing: .12em;
    }

    .ag-hero-empty {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse at 80% 25%, rgba(205, 218, 175, .24), transparent 45%),
            linear-gradient(125deg, #303a25, #55613a);
    }


/* ARTISTS HERO: STATIC PHOTO BACKGROUND */
.ag-artist-hero {
    min-height: 330px;
    margin-bottom: 30px;
    background-color: #303a25;
    background-image:
        linear-gradient(
            90deg,
            rgba(35, 44, 27, .88) 0%,
            rgba(48, 58, 37, .72) 52%,
            rgba(48, 58, 37, .38) 100%
        ),
        url('https://images.unsplash.com/photo-1513364776144-60967b0f800f?auto=format&fit=crop&w=1800&q=85');
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
}

.ag-artist-hero::before {
    background: rgba(38, 48, 28, .12);
}

.ag-artist-hero .ag-hero-copy {
    width: min(100%, 620px);
}

.ag-artist-hero .ag-hero-copy > p {
    max-width: 460px;
}

.ag-artist-hero-decoration {
    display: none;
}

.ag-artist-hero-decoration::before,
.ag-artist-hero-decoration::after {
    content: "";
    position: absolute;
    border: 1px solid rgba(245, 243, 232, .19);
    border-radius: 50%;
}

.ag-artist-hero-decoration::before {
    inset: 25px;
}

.ag-artist-hero-decoration::after {
    inset: 55px;
}

/* EXHIBITIONS HERO: ART GALLERY BACKDROP */
.ag-exhibition-hero {
    min-height: 330px;
    margin-bottom: 30px;
    background-color: #26301f;
    background-image: url('https://images.unsplash.com/photo-1764475552765-9f30797d9728?auto=format&fit=crop&w=1800&q=85');
    background-position: center 48%;
    background-size: cover;
}
.ag-exhibition-hero::before {
    background: linear-gradient(90deg, rgba(25,31,22,.86), rgba(34,42,28,.63) 58%, rgba(34,42,28,.34));
}
.ag-exhibition-hero .ag-hero-copy { width: min(100%, 760px); }
    /* ARTWORK FILTERS */

    .ag-filters {
        margin-top: 24px;
        padding: 18px;
        border: 1px solid var(--line);
        border-radius: 13px;
        background: var(--paper);
    }

    .ag-filter-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 13px;
    }

    .ag-filter-heading strong {
        font-family: Georgia, serif;
        font-size: 18px;
        font-weight: 500;
    }

    .ag-filter-heading span {
        color: var(--muted);
        font-size: 11px;
    }

    .ag-filter-grid {
        display: grid;
        grid-template-columns: minmax(170px, 2fr) repeat(4, minmax(100px, 1fr));
        gap: 9px;
    }

    .ag-filter-grid input,
    .ag-filter-grid select {
        width: 100%;
        min-width: 0;
        height: 42px;
        padding: 0 11px;
        border: 1px solid var(--line);
        border-radius: 7px;
        outline: none;
        background: #fffefb;
        color: var(--dark);
        font: inherit;
        font-size: 11px;
    }

    .ag-filter-grid input:focus,
    .ag-filter-grid select:focus {
        border-color: #89966e;
        box-shadow: 0 0 0 3px rgba(137, 150, 110, .13);
    }

    .ag-filter-grid select {
        cursor: pointer;
    }

    .ag-search {
        position: relative;
    }

    .ag-search .ag-search-icon {
        position: absolute;
        top: 50%;
        left: 11px;
        color: var(--muted);
        font-size: 18px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .ag-search input {
        padding-left: 33px;
    }

    .ag-filter-bottom {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 12px;
        color: var(--muted);
        font-size: 10px;
    }

    .ag-filter-bottom a {
        color: var(--olive);
        font-weight: 700;
        text-decoration: none;
    }

    .art-gallery .visually-hidden {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    /* COLLECTION HEADINGS */

    .ag-collection {
        margin-top: 39px;
        scroll-margin-top: 25px;
    }

    .ag-collection-heading {
        display: flex;
        justify-content: space-between;
        align-items: end;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 18px;
    }

    .ag-collection-heading p {
        margin: 0 0 6px;
        color: var(--muted);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .ag-collection-heading h2 {
        margin: 0;
        color: var(--dark);
        font-family: Georgia, "Times New Roman", serif;
        font-size: clamp(27px, 3vw, 36px);
        font-weight: 400;
        letter-spacing: -.025em;
    }

    .ag-count {
        padding: 7px 12px;
        border: 1px solid var(--line);
        border-radius: 30px;
        background: var(--paper);
        color: var(--muted);
        font-size: 11px;
    }

    /* ARTWORK CARDS */

    .ag-artwork-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .art-gallery .ag-art-card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        overflow: hidden;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: var(--paper);
        color: inherit;
        text-decoration: none;
        transition: transform .22s, box-shadow .22s, border-color .22s;
    }

    .art-gallery .ag-art-card:hover {
        transform: translateY(-3px);
        border-color: #c7ceb7;
        box-shadow: 0 10px 22px rgba(48, 58, 37, .09);
    }

    .art-gallery .ag-art-card-link {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 0;
        color: inherit;
        text-decoration: none;
    }

    .ag-card-save {
        position: absolute;
        z-index: 2;
        top: 10px;
        right: 10px;
    }

    .ag-save-button {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border: 1px solid rgba(48, 58, 37, .16);
        border-radius: 50%;
        background: rgba(255, 254, 249, .96);
        color: #414c2c;
        cursor: pointer;
    }

    .ag-save-button[aria-pressed="true"] {
        color: #b7473d;
    }

    .artwork-save-icon {
        display: block;
        width: 19px;
        height: 22px;
        fill: none;
        stroke: currentColor;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 1.8;
    }

    .ag-save-button[aria-pressed="true"] .artwork-save-icon,
    .artfy-carousel-save-button[aria-pressed="true"] .artwork-save-icon {
        fill: currentColor;
    }

    .ag-save-button:focus-visible,
    .ag-card-save:focus-visible {
        outline: 2px solid #55613a;
        outline-offset: 3px;
    }

    .ag-art-image {
        position: relative;
        width: 100%;
        height: 195px;
        min-height: 195px;
        overflow: hidden;
        background: #f0eee5;
    }

    .ag-art-image img {
        display: block;
        width: 100%;
        height: 100%;
        padding: 7px;
        object-fit: contain;
        object-position: center;
        background: #f0eee5;
        transition: transform .3s;
    }

    .ag-art-card:hover .ag-art-image img {
        transform: scale(1.025);
    }

    .ag-category {
        position: absolute;
        top: 8px;
        left: 8px;
        max-width: calc(100% - 16px);
        overflow: hidden;
        padding: 5px 8px;
        border-radius: 30px;
        background: rgba(255, 254, 249, .94);
        color: var(--dark);
        font-size: 9px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ag-art-meta {
        display: flex;
        flex: 1;
        flex-direction: column;
        min-width: 0;
        padding: 12px;
    }

    .ag-art-meta h3 {
        display: -webkit-box;
        overflow: hidden;
        margin: 0;
        color: var(--dark);
        font-family: Georgia, "Times New Roman", serif;
        font-size: 15px;
        font-weight: 500;
        line-height: 1.4;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .ag-art-artist {
        overflow: hidden;
        margin-top: 5px;
        color: var(--muted);
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ag-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 5px;
        margin-top: auto;
        padding-top: 11px;
    }

    .ag-price {
        overflow-wrap: anywhere;
        color: var(--olive);
        font-size: 11px;
        font-weight: 800;
    }

    .ag-view {
        color: var(--muted);
        font-size: 10px;
        white-space: nowrap;
    }

    .ag-no-image {
        display: grid;
        place-items: center;
        width: 100%;
        height: 100%;
        padding: 15px;
        background: #f0eee5;
        color: #777966;
        font-size: 11px;
        text-align: center;
    }

/* ARTIST DIRECTORY */
.ag-artist-intro {
    display: flex;
    align-items: end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 22px;
}

.ag-artist-intro h2 {
    margin: 0;
    font-family: Georgia, "Times New Roman", serif;
    font-size: clamp(29px, 3vw, 40px);
    font-weight: 400;
    letter-spacing: -.035em;
}

.ag-artist-intro p {
    max-width: 510px;
    margin: 9px 0 0;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.8;
}

/* Compact artist grid */
.ag-artist-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    align-items: stretch;
}

.ag-discovery-shelf {
    margin-top: 38px;
}
.ag-discovery-heading {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 17px;
}
.ag-discovery-heading p {
    margin: 0 0 6px;
    color: var(--muted);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .16em;
    text-transform: uppercase;
}
.ag-discovery-heading h2 {
    margin: 0;
    color: var(--dark);
    font: 400 clamp(27px, 3vw, 36px)/1.12 Georgia, "Times New Roman", serif;
    letter-spacing: -.025em;
}
.ag-discovery-heading a {
    color: var(--olive);
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
}
.ag-discovery-heading a:hover { text-decoration: underline; text-underline-offset: 4px; }
.ag-discovery-artworks,
.ag-discovery-artists {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}
.ag-discovery-artists { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.ag-discovery-card {
    position: relative;
    display: flex;
    min-width: 0;
    overflow: hidden;
    flex-direction: column;
    border: 1px solid var(--line);
    border-radius: 14px;
    background: var(--paper);
    color: inherit;
    text-decoration: none;
    box-shadow: 0 8px 20px rgba(48,58,37,.045);
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}
.ag-discovery-card-link { display: flex; min-width: 0; flex: 1; flex-direction: column; color: inherit; text-decoration: none; }
.ag-discovery-save { position: absolute; z-index: 2; top: 10px; right: 10px; }
.ag-discovery-save.ag-save-button { text-decoration: none; }
.ag-discovery-card:hover {
    transform: translateY(-4px);
    border-color: #c7ceb7;
    box-shadow: 0 15px 30px rgba(48,58,37,.12);
}
.ag-discovery-art-image { height: 220px; overflow: hidden; background: var(--cream); }
.ag-discovery-art-image img,
.ag-discovery-artist-image img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s ease; }
.ag-discovery-card:hover img { transform: scale(1.04); }
.ag-discovery-card-copy { display: grid; gap: 6px; padding: 14px 15px 16px; }
.ag-discovery-card-copy strong { overflow: hidden; color: var(--dark); font: 500 20px/1.2 Georgia, "Times New Roman", serif; text-overflow: ellipsis; white-space: nowrap; }
.ag-discovery-card-copy span,
.ag-discovery-card-copy small { color: var(--muted); font-size: 12px; line-height: 1.5; }
.ag-discovery-card-copy small { color: var(--olive); font-weight: 700; }
.ag-discovery-artist-image { height: 180px; overflow: hidden; background: var(--cream); }
.ag-discovery-artist-fallback { display: grid; width: 100%; height: 100%; place-items: center; background: radial-gradient(circle at 70% 20%,#fffef9,#e7e9d8 68%,#dce1cd); color: var(--olive); font: 56px Georgia,serif; }
@media(max-width:900px) {
    .ag-discovery-artworks { grid-template-columns: repeat(2, minmax(0,1fr)); }
    .ag-discovery-artists { grid-template-columns: repeat(2, minmax(0,1fr)); }
}
@media(max-width:560px) {
    .ag-discovery-shelf { margin-top: 28px; }
    .ag-discovery-artworks,.ag-discovery-artists { grid-template-columns: 1fr; }
    .ag-discovery-art-image { height: 250px; }
}

.art-gallery .ag-artist-card {
    display: flex;
    flex-direction: column;
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--line);
    border-radius: 10px;
    background: var(--paper);
    color: inherit;
    text-decoration: none;
    transition: transform .22s, box-shadow .22s, border-color .22s;
}

.art-gallery .ag-artist-card:hover {
    transform: translateY(-3px);
    border-color: #c7ceb7;
    box-shadow: 0 10px 22px rgba(48, 58, 37, .09);
}

.ag-artist-photo {
    position: relative;
    display: grid;
    place-items: center;
    width: 100%;
    height: 175px;
    overflow: hidden;
    background:
        radial-gradient(
            circle at 80% 20%,
            rgba(210, 221, 182, .4),
            transparent 40%
        ),
        var(--cream);
}

.ag-artist-photo::after {
    content: "";
    position: absolute;
    inset: 8px;
    border: 1px solid rgba(85, 97, 58, .12);
    pointer-events: none;
}

.ag-artist-photo img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform .3s;
}

.ag-artist-card:hover .ag-artist-photo img {
    transform: scale(1.035);
}

.ag-avatar-fallback {
    display: grid;
    place-items: center;
    width: 72px;
    height: 72px;
    border: 1px solid rgba(85, 97, 58, .25);
    border-radius: 50%;
    background: #e8eadc;
    color: var(--olive);
    font-family: Georgia, "Times New Roman", serif;
    font-size: 32px;
}

.ag-artist-card-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: flex-start;
    min-width: 0;
    padding: 13px;
}

.ag-artist-label {
    margin-bottom: 5px;
    color: var(--olive);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
}

.ag-artist-name {
    margin: 0;
    color: var(--dark);
    font-family: Georgia, "Times New Roman", serif;
    font-size: 19px;
    font-weight: 400;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.ag-artist-discipline {
    display: inline-block;
    max-width: 100%;
    margin-top: 7px;
    padding: 5px 8px;
    border: 1px solid var(--line);
    border-radius: 30px;
    background: var(--cream);
    color: var(--olive);
    font-size: 10px;
    overflow-wrap: anywhere;
}

.ag-artist-bio {
    display: -webkit-box;
    overflow: hidden;
    margin: 9px 0 11px;
    color: var(--muted);
    font-size: 11px;
    line-height: 1.65;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
}

.ag-artist-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 10px;
    margin-top: auto;
    padding-top: 10px;
    border-top: 1px solid var(--line);
    color: var(--dark);
    font-size: 11px;
    font-weight: 800;
}

.ag-artist-link span:last-child {
    color: var(--olive);
    font-size: 16px;
    transition: transform .2s;
}

.ag-artist-card:hover .ag-artist-link span:last-child {
    transform: translate(3px, -3px);
}


    /* EXHIBITIONS */

    .ag-exhibition-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .ag-exhibition-card {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #171914;
        color: #fffef9;
        text-decoration: none;
        box-shadow: 0 12px 28px rgba(28, 32, 22, .12);
        transition: transform .25s ease, box-shadow .25s ease;
    }

    .ag-exhibition-card:hover,
    .ag-exhibition-card:focus-visible { transform: translateY(-4px); box-shadow: 0 18px 32px rgba(28, 32, 22, .18); }

    .ag-exhibition-artwork { position: relative; display: grid; min-height: 270px; place-items: center; overflow: hidden; padding: 14px; background: radial-gradient(ellipse at 50% 35%, color-mix(in srgb, var(--exhibition-accent) 50%, #444), #1e211b 75%); }
    .ag-exhibition-artwork::after { position: absolute; inset: 10px; border: 1px solid rgba(255,255,255,.13); border-radius: 7px; content: ""; pointer-events: none; }
    .ag-exhibition-artwork img { display: block; width: 100%; height: 242px; object-fit: contain; filter: drop-shadow(0 12px 16px rgba(0,0,0,.3)); }
    .ag-exhibition-card .ag-exhibition-status { position: absolute; z-index: 1; top: 20px; left: 20px; padding: 7px 10px; border: 1px solid rgba(255,255,255,.65); border-radius: 99px; background: rgba(20,22,18,.72); color: #fff; font-size: 10px; letter-spacing: .12em; text-transform: uppercase; backdrop-filter: blur(8px); }
    .ag-exhibition-poster-fallback { display:flex; width:86%; min-height:220px; flex-direction:column; align-items:center; justify-content:center; gap:17px; padding:24px; border:1px solid rgba(255,255,255,.45); background:linear-gradient(145deg, color-mix(in srgb, var(--exhibition-accent) 82%, #171914), #171914); text-align:center; box-shadow:0 15px 30px rgba(0,0,0,.28); }
    .ag-exhibition-poster-fallback span { color:rgba(255,255,255,.72); font-size:9px; letter-spacing:.18em; }
    .ag-exhibition-poster-fallback strong { font:400 clamp(22px,2.6vw,32px)/1.1 Georgia,serif; }
    .ag-exhibition-poster-fallback small { color:rgba(255,255,255,.72); font-size:10px; letter-spacing:.12em; text-transform:uppercase; }
    .ag-exhibition-card-copy { min-height: 122px; padding: 17px 20px 19px; border-top: 2px solid var(--exhibition-accent); background: linear-gradient(145deg, #282b23, #171914); }

    .ag-exhibition-card strong {
        display: block;
        font-family: Georgia, serif;
        font-size: 22px;
        font-weight: 400;
    }

    .ag-exhibition-card small {
        display: block;
        margin-top: 10px;
        color: rgba(255,255,255,.8);
    }

    .ag-exhibition-card em { display:block; margin-top: 7px; color: color-mix(in srgb, var(--exhibition-accent) 58%, white); font-size: 11px; font-style: normal; letter-spacing: .08em; text-transform: uppercase; }

    .ag-exhibition-card{position:relative;overflow:visible;border:1px solid color-mix(in srgb,var(--exhibition-accent) 26%,#34392c);border-radius:20px;background:linear-gradient(145deg,#272b23,#11140f);box-shadow:0 18px 35px rgba(28,32,22,.2),0 5px 10px rgba(28,32,22,.12);transform-style:preserve-3d;perspective:1000px;transition:transform .45s cubic-bezier(.2,.75,.25,1),box-shadow .45s,border-color .3s}
    .ag-exhibition-card::before{position:absolute;z-index:-1;inset:10px -7px -9px 7px;border:1px solid color-mix(in srgb,var(--exhibition-accent) 32%,transparent);border-radius:20px;content:"";transform:translateZ(-30px);transition:transform .45s}
    .ag-exhibition-card:hover,.ag-exhibition-card:focus-visible{border-color:color-mix(in srgb,var(--exhibition-accent) 72%,white);box-shadow:0 35px 55px rgba(28,32,22,.28),0 12px 24px rgba(28,32,22,.16);transform:perspective(1000px) translateY(-9px) rotateX(2deg) rotateY(-2deg)}
    .ag-exhibition-card:hover::before,.ag-exhibition-card:focus-visible::before{transform:translate3d(0,5px,-36px)}
    .ag-exhibition-artwork{min-height:300px;overflow:hidden;padding:24px;border-radius:19px 19px 0 0;background:radial-gradient(ellipse at 50% 38%,color-mix(in srgb,var(--exhibition-accent) 52%,#92835c),transparent 49%),linear-gradient(145deg,#292e24,#11140f)}
    .ag-exhibition-artwork::before{position:absolute;inset:20px;border:1px solid rgba(255,255,255,.08);border-radius:50%;content:"";transform:rotate(-18deg) scaleY(.45)}
    .ag-exhibition-artwork::after{inset:12px;border-color:rgba(255,255,255,.18);border-radius:11px}
    .ag-exhibition-artwork img{position:relative;z-index:0;width:auto;max-width:78%;height:250px;object-fit:contain;filter:drop-shadow(0 22px 18px rgba(0,0,0,.52));transform:translateZ(34px) rotateY(-6deg) rotateZ(1deg);transition:transform .55s cubic-bezier(.2,.75,.25,1),filter .55s}
    .ag-exhibition-card:hover .ag-exhibition-artwork img{filter:drop-shadow(0 30px 22px rgba(0,0,0,.55));transform:translate3d(0,-7px,54px) rotateY(-2deg) rotateZ(0)}
    .ag-exhibition-card .ag-exhibition-status{top:20px;left:20px;border-color:rgba(255,255,255,.34);background:rgba(14,17,13,.64);box-shadow:0 5px 15px rgba(0,0,0,.15)}
    .ag-exhibition-card-copy{min-height:136px;padding:21px 22px 22px;border-top:1px solid color-mix(in srgb,var(--exhibition-accent) 70%,transparent);border-radius:0 0 19px 19px;background:linear-gradient(145deg,#292e25,#171a15);transform:translateZ(16px)}
    .ag-exhibition-card-copy strong{font-size:24px}
    @media(prefers-reduced-motion:reduce){.ag-exhibition-card,.ag-exhibition-card::before,.ag-exhibition-artwork img{transition:none}}

    /* EMPTY STATE */

    .ag-empty {
        padding: 45px 20px;
        border: 1px dashed #cdd2bf;
        border-radius: 14px;
        background: var(--paper);
        text-align: center;
    }

    .ag-empty-symbol {
        color: var(--olive);
        font-size: 28px;
    }

    .ag-empty h2 {
        margin: 8px 0;
        font-family: Georgia, serif;
        font-size: 27px;
        font-weight: 400;
    }

    .ag-empty p {
        color: var(--muted);
        font-size: 12px;
        line-height: 1.8;
    }

    .ag-empty a {
        display: inline-block;
        margin-top: 8px;
        padding: 10px 15px;
        border-radius: 5px;
        background: var(--dark);
        color: white;
        font-size: 12px;
        text-decoration: none;
    }

    /* RESPONSIVE */

    @media (max-width: 1050px) {
        .ag-artist-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
        .ag-filter-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .ag-search {
            grid-column: span 2;
        }

        .ag-artist-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .ag-hero {
            min-height: 420px;
            padding: 35px 24px 65px;
        }

        .ag-hero h1 {
            font-size: clamp(42px, 9vw, 58px);
        }

        .ag-hero-copy > p {
            font-size: 13px;
        }

        .ag-hero-bottom {
            right: 20px;
            bottom: 18px;
        }

        .ag-artist-hero {
            min-height: 350px;
        }

        .ag-exhibition-hero { min-height: 350px; }

        .ag-artist-hero-decoration {
            right: -110px;
            opacity: .6;
        }

        .ag-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ag-search {
            grid-column: 1 / -1;
        }

        .ag-artwork-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 11px;
        }

        .ag-art-image {
            height: 170px;
            min-height: 170px;
        }

        .ag-art-meta {
            padding: 10px;
        }

        .ag-art-meta h3 {
            font-size: 14px;
        }

       .ag-artist-grid,
.ag-exhibition-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.ag-artist-photo {
    height: 155px;
}

.ag-artist-card-body {
    padding: 12px;
}

.ag-artist-name {
    font-size: 18px;
}

.ag-artist-bio {
    font-size: 11px;
}
    }

    @media (max-width: 430px) {
        .ag-artist-grid {
            grid-template-columns: 1fr;
        }

        .ag-artist-photo {
            aspect-ratio: 5 / 3;
        }

        .ag-artist-card-body {
            padding: 17px;
        }
    }

    @media (max-width: 380px) {
        .ag-hero {
            padding-right: 18px;
            padding-left: 18px;
        }

        .ag-artwork-grid {
            gap: 8px;
        }

        .ag-art-image {
            height: 145px;
            min-height: 145px;
        }

        .ag-art-meta {
            padding: 8px;
        }

        .ag-view {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .art-gallery *,
        .art-gallery *::before,
        .art-gallery *::after {
            scroll-behavior: auto !important;
            animation: none !important;
            transition: none !important;
        }
    }

    /* Exhibition gallery: warm paper, moss and dimensional poster displays. */
    .ag-exhibition-hero{min-height:370px;margin-bottom:34px;border:1px solid rgba(220,211,177,.38);border-radius:28px;background:linear-gradient(90deg,rgba(28,35,23,.78) 0%,rgba(35,43,28,.67) 48%,rgba(35,43,28,.4) 100%),url('https://images.unsplash.com/photo-1643841368618-4aa128ae63c2?auto=format&fit=crop&w=1800&q=85') center 46%/cover no-repeat;color:#f7f4e8;box-shadow:0 26px 65px rgba(38,45,30,.22),inset 0 1px rgba(255,255,255,.18)}
    .ag-exhibition-hero::before{background:linear-gradient(110deg,rgba(20,26,18,.18),rgba(20,26,18,.03) 66%,rgba(20,26,18,.18))}
    .ag-exhibition-hero::after{inset:12px;border:1px solid rgba(85,97,58,.15);border-radius:19px}
    .ag-exhibition-hero .ag-eyebrow{color:#d7c891}
    .ag-exhibition-hero h1{color:#fffdf5;text-wrap:balance}
    .ag-exhibition-hero .ag-hero-copy>p{color:rgba(255,253,245,.86)}
    .ag-exhibition-hero .ag-hero-copy{max-width:720px}
    .ag-exhibition-hero .ag-hero-copy{z-index:2;max-width:min(76%,820px)}
    .ag-exhibition-grid{gap:28px;perspective:1400px}
    .ag-exhibition-card{border-color:#e1ddcd;border-radius:22px;background:#fffdf6;box-shadow:0 18px 34px rgba(65,76,44,.12),0 4px 10px rgba(65,76,44,.06)}
    .ag-exhibition-card::before{border-color:rgba(137,145,103,.28);border-radius:22px}
    .ag-exhibition-card:hover,.ag-exhibition-card:focus-visible{border-color:#b4aa81;box-shadow:0 32px 55px rgba(65,76,44,.2),0 12px 22px rgba(65,76,44,.1);transform:perspective(1100px) translateY(-8px) rotateX(2deg) rotateY(-2deg)}
    .ag-exhibition-artwork{min-height:310px;border-radius:21px 21px 0 0;background:radial-gradient(ellipse at 50% 40%,#f8f2df 0%,#e6e8d8 51%,#d8dfca 100%)}
    .ag-exhibition-artwork::before{border-color:rgba(89,105,65,.17)}
    .ag-exhibition-artwork::after{border-color:rgba(85,97,58,.2)}
    .ag-exhibition-artwork img{max-width:76%;height:255px;filter:drop-shadow(0 19px 14px rgba(61,67,45,.25));transform:translateZ(34px) rotateY(-6deg) rotateZ(1deg)}
    .ag-exhibition-poster-fallback{border-color:rgba(255,255,255,.8);background:radial-gradient(circle at 80% 18%,rgba(255,255,255,.72),transparent 36%),linear-gradient(145deg,#9aa17a,#62724d);color:#fffdf3;box-shadow:0 18px 30px rgba(65,76,44,.18)}
    .ag-exhibition-poster-fallback span,.ag-exhibition-poster-fallback small{color:rgba(255,253,243,.78)}
    .ag-exhibition-card:hover .ag-exhibition-artwork img{filter:drop-shadow(0 28px 18px rgba(61,67,45,.3));transform:translate3d(0,-7px,54px) rotateY(-2deg)}
    .ag-exhibition-card .ag-exhibition-status{border-color:rgba(85,97,58,.2);background:rgba(255,253,246,.86);color:#4b5b39;box-shadow:0 5px 15px rgba(65,76,44,.08)}
    .ag-exhibition-card-copy{border-top:1px solid color-mix(in srgb,var(--exhibition-accent) 44%,#e7e2d1);border-radius:0 0 21px 21px;background:linear-gradient(145deg,#fffdf6,#f3f0e5);color:#35422d}
    .ag-exhibition-card-copy strong{color:#35422d}
    .ag-exhibition-card-copy small{color:#747a62}
    .ag-exhibition-card em{color:#79824e}
    @media(max-width:700px){.ag-exhibition-grid{gap:18px}.ag-exhibition-artwork{min-height:250px}.ag-exhibition-artwork img{height:205px}}
    .ag-exhibition-shelf{margin:46px 0 58px}
    .ag-exhibition-shelf-heading{display:flex;align-items:end;justify-content:space-between;gap:16px;margin:0 0 20px;padding-bottom:15px;border-bottom:1px solid #dfddcf}
    .ag-exhibition-shelf-heading p{margin:0 0 6px;color:#77805d;font-size:10px;font-weight:800;letter-spacing:.16em;text-transform:uppercase}
    .ag-exhibition-shelf-heading h2{margin:0;color:#35422d;font:normal clamp(28px,3.2vw,40px)/1.1 Georgia,serif}
    .ag-exhibition-shelf-heading small{color:#7c806e;font-size:11px}
    .ag-exhibition-grid-featured{grid-template-columns:1fr;gap:24px}
    .ag-exhibition-card.is-ongoing-featured{display:grid;width:calc(100% - 32px);max-width:900px;margin:0 auto;grid-template-columns:44% 56%;min-height:440px}
    .ag-exhibition-card.is-ongoing-featured .ag-exhibition-artwork{position:relative;min-height:440px;padding:0;border-radius:21px 0 0 21px}
    .ag-exhibition-card.is-ongoing-featured .ag-exhibition-artwork img{position:absolute;inset:0;width:100%;height:100%;max-width:none;object-fit:cover;object-position:center;filter:none;transform:none}
    .ag-exhibition-card.is-ongoing-featured .ag-exhibition-card-copy{display:flex;flex-direction:column;justify-content:center;min-height:100%;padding:clamp(24px,3vw,42px);border-top:0;border-left:1px solid #e7e2d1;border-radius:0 21px 21px 0}
    .ag-exhibition-card.is-ongoing-featured .ag-exhibition-card-copy strong{font-size:clamp(28px,3vw,40px);line-height:1.08}
    .ag-featured-kicker{margin-bottom:12px;color:#788253;font-size:10px;font-weight:800;letter-spacing:.18em}
    .ag-exhibition-cta{display:inline-flex;align-items:center;gap:10px;width:max-content;margin-top:25px;padding:12px 16px;border-radius:8px;background:#55613a;color:#fffdf5;font-size:12px;font-weight:700}
    .ag-exhibition-shelf-past .ag-exhibition-card{opacity:.88}
    @media(max-width:700px){.ag-exhibition-shelf{margin:34px 0 42px}.ag-exhibition-shelf-heading{align-items:flex-start;flex-direction:column}.ag-exhibition-card.is-ongoing-featured{grid-template-columns:1fr;min-height:0}.ag-exhibition-card.is-ongoing-featured .ag-exhibition-artwork{min-height:320px;border-radius:21px 21px 0 0}.ag-exhibition-card.is-ongoing-featured .ag-exhibition-card-copy{min-height:220px;border-top:1px solid #e7e2d1;border-left:0;border-radius:0 0 21px 21px}}
</style>

<div class="art-gallery">

    {{-- HERO --}}
    @if ($pageType === 'artists')
        <section class="ag-hero ag-artist-hero">
            <div class="ag-artist-hero-decoration" aria-hidden="true"></div>

            <div class="ag-hero-copy">
                <span class="ag-eyebrow">The people behind the art</span>

                <h1>Meet the minds<br>behind <em>the art.</em></h1>

                <p>
                    Every artist has a story, a perspective, and a unique way
                    of seeing the world. Get to know the creatives in our
                    community and explore the people behind their work.
                </p>

                <a href="#listing-results" class="ag-hero-button">
                    Meet our artists <span aria-hidden="true">↘</span>
                </a>
            </div>
        </section>
    @else
        <section class="ag-hero {{ $pageType === 'exhibitions' ? 'ag-exhibition-hero' : '' }}">
            @if ($pageType === 'artworks')
                <div class="ag-hero-background" id="agHeroBackground">
                    @forelse ($featuredArtworks as $index => $item)
                        @php
                            $backgroundImage = $resolveArtfyImage($item['image'] ?? null);
                        @endphp

                        <div
                            class="ag-background-slide {{ $index === 0 ? 'is-active' : '' }}"
                            data-hero-slide="{{ $index }}"
                            style="background-image: url('{{ $backgroundImage }}');"
                            role="img"
                            aria-label="{{ $item['title'] ?? 'Featured artwork' }}"
                        ></div>
                    @empty
                        <div class="ag-hero-empty"></div>
                    @endforelse
                </div>
            @endif

            <div class="ag-hero-copy">
                <span class="ag-eyebrow">
                    {{ $pageType === 'artworks' ? 'The Artfy collection' : $eyebrow }}
                </span>

                @if ($pageType === 'artworks')
                    <h1>Art with<br><em>depth &amp; soul.</em></h1>

                    <p>
                        Explore original artwork uploaded by independent artists.
                        Discover different perspectives, find your favourite pieces,
                        and bring creativity closer to you.
                    </p>

                    <a href="#all-artworks" class="ag-hero-button">
                        Explore all artworks <span aria-hidden="true">↘</span>
                    </a>
                @else
                    <h1>{{ $title }}</h1>
                    <p>{{ $description }}</p>
                @endif
            </div>

            @if ($pageType === 'artworks' && $featuredArtworks->count() > 1)
                <div class="ag-hero-bottom" aria-label="Artwork slideshow controls">
                    <span class="ag-hero-number" id="agHeroNumber">
                        01 / {{ str_pad($featuredArtworks->count(), 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <div class="ag-hero-indicators">
                        @foreach ($featuredArtworks as $index => $item)
                            <button
                                type="button"
                                class="ag-hero-dot {{ $index === 0 ? 'is-active' : '' }}"
                                data-hero-dot="{{ $index }}"
                                aria-label="Show artwork {{ $index + 1 }}"
                                aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                            ></button>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    @endif

    @if ($pageType === 'artworks' && !empty($showDiscoverySections) && $showDiscoverySections)
        @if (count($topArtworks))
        <section class="ag-discovery-shelf" aria-labelledby="top-artworks-heading">
            <div class="ag-discovery-heading">
                <div><p>Most loved by the community</p><h2 id="top-artworks-heading">Top artworks</h2></div>
                <a href="#all-artworks">Browse all artworks ↘</a>
            </div>
            <div class="ag-discovery-artworks">
                @foreach ($topArtworks as $item)
                    @include('artfy.partials.discovery-artwork-card', ['item' => $item])
                @endforeach
            </div>
        </section>
        @endif

        @if (count($newArtworks))
        <section class="ag-discovery-shelf" aria-labelledby="new-artworks-heading">
            <div class="ag-discovery-heading">
                <div><p>Uploaded in the last 3 days</p><h2 id="new-artworks-heading">New artworks</h2></div>
                <a href="#all-artworks">See the full collection ↘</a>
            </div>
            <div class="ag-discovery-artworks">
                @foreach ($newArtworks as $item)
                    @include('artfy.partials.discovery-artwork-card', ['item' => $item])
                @endforeach
            </div>
        </section>
        @endif
    @elseif ($pageType === 'artists')
        @if (count($topArtists))
        <section class="ag-discovery-shelf" aria-labelledby="top-artists-heading">
            <div class="ag-discovery-heading">
                <div><p>Followed by the Artfy community</p><h2 id="top-artists-heading">Top artists</h2></div>
                <a href="#listing-results">Meet every artist ↘</a>
            </div>
            <div class="ag-discovery-artists">
                @foreach ($topArtists as $item)
                    @include('artfy.partials.discovery-artist-card', ['item' => $item])
                @endforeach
            </div>
        </section>
        @endif

        @if (count($newArtists))
        <section class="ag-discovery-shelf" aria-labelledby="new-artists-heading">
            <div class="ag-discovery-heading">
                <div><p>Joined in the last 3 days</p><h2 id="new-artists-heading">New artists</h2></div>
                <a href="#listing-results">Explore the community ↘</a>
            </div>
            <div class="ag-discovery-artists">
                @foreach ($newArtists as $item)
                    @include('artfy.partials.discovery-artist-card', ['item' => $item])
                @endforeach
            </div>
        </section>
        @endif
    @endif

    {{-- ARTWORK FILTERS: ONLY SHOWN ON ARTWORKS PAGE --}}
    @if ($pageType === 'artworks')
        <section class="ag-filters" aria-label="Search and filter artworks">
            <div class="ag-filter-heading">
                <strong>Find your next favourite</strong>
                <span>Search · Refine · Discover</span>
            </div>

            <form
                id="artworkFilters"
                class="ag-filter-grid"
                action="{{ route('artworks.index') }}"
                method="GET"
            >
                <label class="ag-search">
                    <span class="ag-search-icon" aria-hidden="true">⌕</span>
                    <span class="visually-hidden">Search artworks or artists</span>
                    <input
                        type="search"
                        name="q"
                        value="{{ $query ?? '' }}"
                        placeholder="Search art or artist..."
                    >
                </label>

                <label>
                    <span class="visually-hidden">Artwork format</span>
                    <select name="artwork_type" aria-label="Artwork format">
                        <option value="">All formats</option>
                        <option value="physical" @selected(($artworkFormat ?? '') === 'physical')>Physical</option>
                        <option value="digital" @selected(($artworkFormat ?? '') === 'digital')>Digital</option>
                    </select>
                </label>

                <label>
                    <span class="visually-hidden">Artistic medium</span>
                    <select name="medium" aria-label="Artistic medium">
                        <option value="">All mediums</option>
                        @foreach ([
                            'oil painting' => 'Oil painting',
                            'watercolor painting' => 'Watercolor',
                            'pastel painting' => 'Pastel',
                            'acrylic painting' => 'Acrylic',
                            'ink wash painting' => 'Ink wash',
                            'encaustic painting' => 'Encaustic',
                            'spray painting' => 'Spray painting',
                            'sketching' => 'Sketching',
                            'photography' => 'Photography',
                            'digital painting' => 'Digital painting',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(($mediumFilter ?? '') === $value)
                            >{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="visually-hidden">Maximum budget</span>
                    <select name="max_price" aria-label="Maximum budget">
                        <option value="">Any budget</option>
                        @foreach ([2000, 3000, 5000, 10000, 20000] as $amount)
                            <option
                                value="{{ $amount }}"
                                @selected((string) ($maxPrice ?? '') === (string) $amount)
                            >
                                Up to ৳{{ number_format($amount) }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="visually-hidden">Sort artworks</span>
                    <select name="sort" aria-label="Sort artworks">
                        <option value="latest" @selected(($sort ?? 'latest') === 'latest')>Newest first</option>
                        <option value="oldest" @selected(($sort ?? '') === 'oldest')>Oldest first</option>
                        <option value="price-low" @selected(($sort ?? '') === 'price-low')>Price: low to high</option>
                        <option value="price-high" @selected(($sort ?? '') === 'price-high')>Price: high to low</option>
                        <option value="popular" @selected(($sort ?? '') === 'popular')>Most popular</option>
                    </select>
                </label>
            </form>

            <div class="ag-filter-bottom">
                <span>Discover art that fits your taste and budget.</span>
                <a href="{{ route('artworks.index') }}">Clear filters ↗</a>
            </div>
        </section>
    @endif

    {{-- LISTING RESULTS --}}
    <section
        class="ag-collection"
        id="{{ $pageType === 'artworks' ? 'all-artworks' : 'listing-results' }}"
    >
        @if ($pageType === 'artworks')
            <div class="ag-collection-heading">
                <div>
                    <p>Collected with creativity</p>
                    <h2>All artworks</h2>
                </div>

                <span class="ag-count">
                    {{ count($items) }}
                    {{ count($items) === 1 ? 'artwork' : 'artworks' }}
                </span>
            </div>
        @elseif ($pageType === 'artists')
            <div class="ag-artist-intro">
                <div>
                    <h2>Our creative community</h2>
                    <p>
                        Discover the artists, learn about their creative interests,
                        and visit their profiles to explore their portfolios.
                    </p>
                </div>

                <span class="ag-count">
                    {{ count($items) }}
                    {{ count($items) === 1 ? 'artist' : 'artists' }}
                </span>
            </div>
        @endif

        @if (count($items) === 0)
            <div class="ag-empty">
                <div class="ag-empty-symbol" aria-hidden="true">✳</div>

                <h2>
                    @if ($pageType === 'artworks')
                        No artworks found
                    @elseif ($pageType === 'artists')
                        Our community is growing
                    @else
                        Nothing here just yet
                    @endif
                </h2>

                <p>
                    @if ($pageType === 'artworks')
                        Try another search or clear your filters to see the full collection.
                    @elseif ($pageType === 'artists')
                        There are no artists to display right now. Check back as the Artfy community grows.
                    @else
                        Check back as the Artfy community grows.
                    @endif
                </p>

                @if ($pageType === 'artworks')
                    <a href="{{ route('artworks.index') }}">See all artworks</a>
                @endif
            </div>

        @elseif ($pageType === 'artworks')
            <div class="ag-artwork-grid">
                @foreach ($items as $item)
                    @php
                        $cardImage = $resolveArtfyImage($item['image'] ?? null);
                    @endphp

                    <article class="ag-art-card">
                        <a
                            class="ag-art-card-link"
                            href="{{ route('artworks.show', $item['slug']) }}"
                        >
                        <div class="ag-art-image">
                            @if ($cardImage)
                                <img
                                    src="{{ $cardImage }}"
                                    alt="{{ $item['title'] ?? 'Artwork' }}"
                                    loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.hidden=false;"
                                >
                                <div class="ag-no-image" hidden>
                                    Image could not be loaded.
                                </div>
                            @else
                                <div class="ag-no-image">
                                    No artwork image available
                                </div>
                            @endif

                            <span class="ag-category">
                                {{ $item['category'] ?? 'Other' }}
                            </span>
                        </div>

                        <div class="ag-art-meta">
                            <h3>{{ $item['title'] ?? 'Untitled artwork' }}</h3>

                            <div class="ag-art-artist">
                                by {{ $item['artist'] ?? 'Artfy Artist' }}
                            </div>

                            <div class="ag-card-footer">
                                <span class="ag-price">
                                    @if (isset($item['price']) && $item['price'] !== null)
                                        ৳{{ number_format((float) $item['price'], 2) }}
                                    @else
                                        Price not listed
                                    @endif
                                </span>

                                <span class="ag-view">View ↗</span>
                            </div>
                        </div>
                        </a>

                        @auth
                            @if (auth()->user()->isArtLover())
                                <form
                                    class="ag-card-save"
                                    action="{{ route('artworks.favorite', $item['id']) }}"
                                    method="POST"
                                >
                                    @csrf
                                    <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                                    <button
                                        class="ag-save-button"
                                        type="submit"
                                        aria-label="{{ $item['is_saved'] ? 'Remove from favorites' : 'Save to favorites' }}"
                                        aria-pressed="{{ $item['is_saved'] ? 'true' : 'false' }}"
                                    >
                                        <svg class="artwork-save-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M6 4.5A2.5 2.5 0 0 1 8.5 2h7A2.5 2.5 0 0 1 18 4.5V22l-6-4-6 4V4.5Z"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        @else
                            <a
                                class="ag-card-save ag-save-button"
                                href="{{ route('login', ['redirect' => url()->current()]) }}"
                                aria-label="Log in to save to favorites"
                            >
                                <svg class="artwork-save-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 4.5A2.5 2.5 0 0 1 8.5 2h7A2.5 2.5 0 0 1 18 4.5V22l-6-4-6 4V4.5Z"/>
                                </svg>
                            </a>
                        @endauth
                    </article>
                @endforeach
            </div>

        @elseif ($pageType === 'artists')
            <div class="ag-artist-grid">
                @foreach ($items as $item)
                    @php
                        /*
                         * GalleryController supplies the artist's profile_photo
                         * through the array key "image".
                         */
                        $artistImage = $resolveArtfyImage($item['image'] ?? null);
                        $artistName = $item['name'] ?? 'Artfy Artist';
                        $artistInitial = mb_strtoupper(
                            mb_substr($artistName, 0, 1)
                        );
                    @endphp

                    <a
                        class="ag-artist-card"
                        href="{{ route('artists.show', $item['slug']) }}"
                    >
                        <div class="ag-artist-photo">
                            @if ($artistImage)
                                <img
                                    src="{{ $artistImage }}"
                                    alt="Portrait of {{ $artistName }}"
                                    loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.hidden=false;"
                                >
                                <div
                                    class="ag-avatar-fallback"
                                    hidden
                                    aria-hidden="true"
                                >{{ $artistInitial }}</div>
                            @else
                                <div
                                    class="ag-avatar-fallback"
                                    aria-hidden="true"
                                >{{ $artistInitial }}</div>
                            @endif
                        </div>

                        <div class="ag-artist-card-body">
                            <span class="ag-artist-label">Artfy artist</span>

                            <h3 class="ag-artist-name">
                                {{ $artistName }}
                            </h3>

                            <span class="ag-artist-discipline">
                                {{ $item['discipline'] ?? 'Independent artist' }}
                            </span>

                            <p class="ag-artist-bio">
                                {{ $item['bio'] ?? 'Discover original artwork and the creative journey of this independent artist.' }}
                            </p>

                            <div class="ag-artist-link">
                                <span>View artist profile</span>
                                <span aria-hidden="true">↗</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

        @else
            @php
                $exhibitionGroups = [
                    ['state' => 'Ongoing', 'eyebrow' => 'Happening now', 'title' => 'Ongoing exhibitions', 'note' => 'Step inside and explore the art on view.', 'class' => 'ag-exhibition-shelf-live', 'featured' => true],
                    ['state' => 'Upcoming', 'eyebrow' => 'Coming soon', 'title' => 'Upcoming exhibitions', 'note' => 'Get ready for the next collection of stories.', 'class' => 'ag-exhibition-shelf-upcoming', 'featured' => false],
                    ['state' => 'Recently closed', 'eyebrow' => 'Past collections', 'title' => 'Recently closed', 'note' => 'Revisit exhibitions that have finished.', 'class' => 'ag-exhibition-shelf-past', 'featured' => false],
                ];
                $groupedExhibitions = collect($items)->groupBy('exhibition_state');
            @endphp

            @foreach ($exhibitionGroups as $group)
                @php($groupItems = $groupedExhibitions->get($group['state'], collect()))
                @if ($groupItems->isNotEmpty())
                    <section class="ag-exhibition-shelf {{ $group['class'] }}" aria-labelledby="exhibition-heading-{{ \Illuminate\Support\Str::slug($group['state']) }}">
                        <div class="ag-exhibition-shelf-heading">
                            <div>
                                <p>{{ $group['eyebrow'] }}</p>
                                <h2 id="exhibition-heading-{{ \Illuminate\Support\Str::slug($group['state']) }}">{{ $group['title'] }}</h2>
                            </div>
                            <small>{{ $group['note'] }}</small>
                        </div>
                        <div class="ag-exhibition-grid {{ $group['featured'] ? 'ag-exhibition-grid-featured' : '' }}">
                            @foreach ($groupItems as $item)
                                @include('artfy.partials.exhibition-card', ['item' => $item, 'featured' => $group['featured']])
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        @endif
    </section>
</div>

@if ($pageType === 'artworks')
<script>
document.addEventListener('DOMContentLoaded', function () {
    /* Automatically submit artwork filters */
    const filters = document.getElementById('artworkFilters');

    if (filters) {
        filters.querySelectorAll('select').forEach(function (select) {
            select.addEventListener('change', function () {
                filters.requestSubmit();
            });
        });

        const search = filters.querySelector('input[name="q"]');

        if (search) {
            search.addEventListener('search', function () {
                filters.requestSubmit();
            });

            search.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    filters.requestSubmit();
                }
            });
        }
    }

    /* Artwork hero slideshow */
    const background = document.getElementById('agHeroBackground');

    if (!background) return;

    const slides = Array.from(
        background.querySelectorAll('.ag-background-slide')
    );

    const dots = Array.from(
        document.querySelectorAll('.ag-hero-dot')
    );

    const numberLabel = document.getElementById('agHeroNumber');

    if (slides.length < 2) return;

    let activeIndex = 0;
    let rotationTimer = null;

    const pad = function (number) {
        return String(number).padStart(2, '0');
    };

    function showSlide(index) {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach(function (slide, position) {
            slide.classList.toggle('is-active', position === activeIndex);
        });

        dots.forEach(function (dot, position) {
            const active = position === activeIndex;

            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (numberLabel) {
            numberLabel.textContent =
                pad(activeIndex + 1) + ' / ' + pad(slides.length);
        }
    }

    function stopRotation() {
        if (rotationTimer) {
            clearInterval(rotationTimer);
            rotationTimer = null;
        }
    }

    function startRotation() {
        stopRotation();

        if (slides.length < 2 || document.hidden) return;

        rotationTimer = setInterval(function () {
            showSlide(activeIndex + 1);
        }, 4000);
    }

    dots.forEach(function (dot, index) {
        dot.addEventListener('click', function () {
            showSlide(index);
            startRotation();
        });
    });

    const hero = document.querySelector('.ag-hero');

    if (hero) {
        hero.addEventListener('mouseenter', stopRotation);
        hero.addEventListener('mouseleave', startRotation);
        hero.addEventListener('focusin', stopRotation);
        hero.addEventListener('focusout', startRotation);
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopRotation();
        } else {
            startRotation();
        }
    });

    showSlide(0);
    startRotation();
});
</script>
@endif
@endsection
