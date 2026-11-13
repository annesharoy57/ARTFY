@extends('admin.layout')

@section('title', 'All Art Lovers | Artfy Admin')

@section('page_eyebrow', 'Artfy / User Management')

@section('page_title', 'Art Lover Community')

@section('page_intro', 'Meet the people discovering and supporting independent art on Artfy.')

@push('styles')
<style>
    .art-lovers-page {
        --directory-olive: #4d5b37;
        --directory-gold: #c4a568;
        width: 100%;
    }

    .art-lovers-hero {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        min-height: 190px;
        overflow: hidden;
        padding: 30px 34px;
        margin: 4px 0 28px;
        border: 1px solid #dedfce;
        border-radius: 22px;
        background:
            radial-gradient(circle at 87% 22%, rgba(197, 166, 104, .23), transparent 24%),
            radial-gradient(circle at 100% 110%, rgba(85, 97, 58, .15), transparent 38%),
            linear-gradient(115deg, #fffefa, #eeefe3);
        box-shadow: 0 16px 40px rgba(65, 76, 44, .075);
    }

    .art-lovers-hero::after {
        position: absolute;
        right: 15%;
        bottom: -100px;
        width: 250px;
        height: 250px;
        border: 1px solid rgba(85, 97, 58, .17);
        border-radius: 50%;
        content: '';
        pointer-events: none;
    }

    .art-lovers-hero-copy,
    .art-lovers-total { position: relative; z-index: 1; }

    .art-lovers-kicker {
        display: block;
        margin-bottom: 10px;
        color: #80876a;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .art-lovers-hero h2 {
        margin: 0;
        color: #3e4a2e;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: clamp(26px, 3.1vw, 38px);
        font-weight: 400;
        letter-spacing: -.025em;
        line-height: 1.16;
    }

    .art-lovers-hero p {
        max-width: 470px;
        margin: 11px 0 0;
        color: #747a64;
        font-size: 13px;
        line-height: 1.75;
    }

    .art-lovers-total {
        display: flex;
        align-items: center;
        gap: 15px;
        min-width: 190px;
        padding: 15px 18px;
        border: 1px solid rgba(85, 97, 58, .13);
        border-radius: 17px;
        background: rgba(255, 255, 255, .64);
        backdrop-filter: blur(10px);
    }

    .art-lovers-total-icon {
        display: grid;
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        place-items: center;
        border-radius: 14px;
        background: #e8eadb;
        color: var(--directory-olive);
    }

    .art-lovers-total-icon svg { width: 22px; height: 22px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
    .art-lovers-total strong { display: block; color: #414c2c; font-family: Georgia, serif; font-size: 27px; font-weight: 500; line-height: 1; }
    .art-lovers-total span:last-child { display: block; margin-top: 5px; color: #777c68; font-size: 10px; letter-spacing: .04em; }

    .art-lovers-section-heading {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 14px;
        margin: 0 2px 14px;
    }

    .art-lovers-section-heading h3 {
        margin: 0;
        color: #414c2c;
        font-family: Georgia, 'Times New Roman', serif;
        font-size: 22px;
        font-weight: 400;
    }

    .art-lovers-section-heading p { margin: 5px 0 0; color: #77786e; font-size: 12px; }

    .art-lovers-page-count {
        flex-shrink: 0;
        padding: 7px 11px;
        border: 1px solid #e1e2d5;
        border-radius: 999px;
        background: #f5f4ec;
        color: #687253;
        font-size: 11px;
        font-weight: 600;
    }

    .art-lovers-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .art-lover-card {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
        padding: 19px 20px;
        border: 1px solid #e5e3d7;
        border-radius: 17px;
        background: rgba(255, 255, 255, .88);
        box-shadow: 0 7px 20px rgba(65, 76, 44, .035);
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .art-lover-card:hover {
        transform: translateY(-3px);
        border-color: #c9cfb8;
        box-shadow: 0 14px 30px rgba(65, 76, 44, .09);
    }

    .art-lover-avatar {
        position: relative;
        display: grid;
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        place-items: center;
        overflow: hidden;
        border: 3px solid #fff;
        border-radius: 50%;
        background: linear-gradient(145deg, #d8dcc6, #b7c09d);
        box-shadow: 0 3px 12px rgba(65, 76, 44, .14);
        color: #455235;
        font-family: Georgia, serif;
        font-size: 23px;
    }

    .art-lover-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .art-lover-avatar-fallback { display: grid; position: absolute; inset: 0; place-items: center; }
    .art-lover-avatar-fallback[hidden] { display: none; }
    .art-lover-main { flex: 1; min-width: 0; }
    .art-lover-name { margin: 0 0 4px; color: #303528; font-size: 15px; font-weight: 700; overflow-wrap: anywhere; }
    .art-lover-email { margin: 0; color: #747969; font-size: 12px; line-height: 1.55; overflow-wrap: anywhere; }
    .art-lover-meta { display: flex; flex-wrap: wrap; gap: 8px 13px; margin-top: 8px; color: #8a8d7d; font-size: 10px; }
    .art-lover-meta span { display: inline-flex; align-items: center; gap: 5px; }
    .art-lover-meta svg { width: 13px; height: 13px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }

    .art-lover-view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        flex: 0 0 auto;
        padding: 10px 13px;
        border: 1px solid #d9ddcc;
        border-radius: 999px;
        background: #f4f5ed;
        color: #4d5b37;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        transition: color .2s ease, background .2s ease, border-color .2s ease;
    }

    .art-lover-view:hover { border-color: #4d5b37; background: #4d5b37; color: #fff; }
    .art-lover-view:focus-visible { outline: 3px solid rgba(85, 97, 58, .25); outline-offset: 3px; }

    .art-lovers-empty {
        padding: 54px 24px;
        border: 1px dashed #d7dac9;
        border-radius: 20px;
        background: linear-gradient(145deg, #fffefa, #f0f0e6);
        text-align: center;
    }

    .art-lovers-empty-icon { display: grid; width: 58px; height: 58px; margin: 0 auto 15px; place-items: center; border-radius: 18px; background: #e8eadb; color: #4d5b37; }
    .art-lovers-empty-icon svg { width: 27px; height: 27px; fill: none; stroke: currentColor; stroke-width: 1.6; stroke-linecap: round; stroke-linejoin: round; }
    .art-lovers-empty h3 { margin: 0 0 8px; color: #414c2c; font-family: Georgia, serif; font-size: 23px; font-weight: 400; }
    .art-lovers-empty p { max-width: 420px; margin: auto; color: #77786e; font-size: 13px; line-height: 1.7; }
    .art-lovers-pagination { margin-top: 24px; overflow-x: auto; }
    .art-lovers-pagination nav { display: flex; justify-content: center; }
    .art-lovers-footer { margin-top: 28px; padding-top: 16px; border-top: 1px solid #e5e3d7; color: #858878; font-size: 11px; }

    @media (max-width: 920px) {
        .art-lovers-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 620px) {
        .art-lovers-hero { align-items: flex-start; flex-direction: column; padding: 24px; }
        .art-lovers-total { min-width: 0; width: 100%; }
        .art-lover-card { align-items: flex-start; flex-wrap: wrap; padding: 17px; }
        .art-lover-main { padding-top: 5px; }
        .art-lover-view { margin-left: 74px; }
        .art-lovers-section-heading { align-items: flex-start; flex-direction: column; }
    }

    @media (prefers-reduced-motion: reduce) {
        .art-lover-card, .art-lover-view { transition: none; }
    }
</style>
@endpush

@section('content')
<div class="art-lovers-page">
    <section class="art-lovers-hero" aria-label="Art lover community overview">
        <div class="art-lovers-hero-copy">
            <span class="art-lovers-kicker">The Artfy community</span>
            <h2>A community that<br>sees art differently.</h2>
            <p>Browse registered art lovers and open a profile to see more about their Artfy account.</p>
        </div>

        <div class="art-lovers-total">
            <span class="art-lovers-total-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20"/><circle cx="9.5" cy="7" r="3.5"/><path d="M17 11a3.5 3.5 0 1 0-1-6.85M17 14.5a4 4 0 0 1 4 4V20"/></svg>
            </span>
            <span>
                <strong>{{ number_format($artLovers->total()) }}</strong>
                <span>registered art lovers</span>
            </span>
        </div>
    </section>

    <section aria-labelledby="art-lovers-list-title">
        <div class="art-lovers-section-heading">
            <div>
                <h3 id="art-lovers-list-title">Community directory</h3>
                <p>Art lovers who have joined Artfy.</p>
            </div>
            <span class="art-lovers-page-count">
                Showing {{ $artLovers->count() }} of {{ $artLovers->total() }}
            </span>
        </div>

        @if ($artLovers->isNotEmpty())
            <div class="art-lovers-grid">
                @foreach ($artLovers as $artLover)
                    @php
                        $loverName = $artLover->name ?: 'Art lover';
                        $loverInitial = mb_strtoupper(mb_substr($loverName, 0, 1));
                    @endphp

                    <article class="art-lover-card">
                        <div class="art-lover-avatar" aria-hidden="true">
                            @if (!empty($artLover->profile_photo))
                                <img
                                    src="{{ asset('storage/' . ltrim($artLover->profile_photo, '/')) }}"
                                    alt=""
                                    loading="lazy"
                                    onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                                >
                                <span class="art-lover-avatar-fallback" hidden>{{ $loverInitial }}</span>
                            @else
                                {{ $loverInitial }}
                            @endif
                        </div>

                        <div class="art-lover-main">
                            <h4 class="art-lover-name">{{ $loverName }}</h4>
                            <p class="art-lover-email">{{ $artLover->email }}</p>
                            <div class="art-lover-meta">
                                <span>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>
                                    Joined {{ $artLover->created_at?->format('d M Y') ?? 'date unavailable' }}
                                </span>
                                <span>Member #{{ $artLover->id }}</span>
                            </div>
                        </div>

                        <a href="{{ route('admin.art-lovers.show', $artLover) }}" class="art-lover-view">
                            View profile <span aria-hidden="true">&rarr;</span>
                        </a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="art-lovers-empty">
                <span class="art-lovers-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20v-1.2a4 4 0 0 1 4-4h5a4 4 0 0 1 4 4V20M16 4.8a3.5 3.5 0 0 1 0 6.4M18 15a4 4 0 0 1 3.5 4v1"/></svg>
                </span>
                <h3>Your community is just beginning</h3>
                <p>Registered art lovers will appear here as they join Artfy.</p>
            </div>
        @endif
    </section>

    @if ($artLovers->hasPages())
        <div class="art-lovers-pagination">
            {{ $artLovers->links() }}
        </div>
    @endif

    <footer class="art-lovers-footer">ARTFY Administration · Art Lover Directory</footer>
</div>
@endsection
