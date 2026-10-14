@extends('artfy.layout')

@section('title', $title)

@section('content')
    <section class="page-intro simple-intro">
        <span class="eyebrow">{{ $eyebrow }}</span><h1>{{ $title }}</h1><p>{{ $description }}</p>
    </section>
    <section class="section utility-section">
        @if (in_array($pageType, ['login', 'register'], true))
            <div class="utility-card">
                <span class="utility-icon">✳</span><h2>{{ $pageType === 'login' ? 'Your art journey is waiting.' : 'A creative community awaits.' }}</h2>
                <p>Account features are not connected yet. For now, you can browse the gallery and discover artists and exhibitions.</p>
                <div class="utility-actions"><a class="button button-dark" href="{{ route('artworks.index') }}">Explore artworks <span>→</span></a><a class="text-link" href="{{ $pageType === 'login' ? route('register') : route('login') }}">{{ $pageType === 'login' ? 'Create an account' : 'Already have an account?' }}</a></div>
            </div>
        @elseif ($pageType === 'favorites')
            <div class="utility-card">
                <span class="utility-icon">♡</span><h2>Keep the pieces you love.</h2><p>Browse the collection and open any artwork to find its save-to-favorites link.</p>
                <a class="button button-dark" href="{{ route('artworks.index') }}">Find your favorites <span>→</span></a>
            </div>
        @elseif ($pageType === 'studio')
            <div class="utility-card">
                <span class="utility-icon">✦</span><h2>Make space for your work.</h2><p>The artist workspace is ready to grow. Explore the gallery or find out how the artist pages are organized.</p>
                <div class="utility-actions"><a class="button button-dark" href="{{ route('artists.index') }}">Meet the artists <span>→</span></a><a class="text-link" href="{{ route('register') }}">Join Artfy</a></div>
            </div>
        @else
            <div class="utility-card">
                <span class="utility-icon">⌘</span><h2>Everything in one place.</h2><p>The admin dashboard entry point is connected. User, artwork, and exhibition management needs the admin backend to be implemented.</p>
                <div class="utility-actions"><a class="button button-dark" href="{{ route('artworks.index') }}">View artworks <span>→</span></a><a class="text-link" href="{{ route('exhibitions.index') }}">Manage exhibitions</a></div>
            </div>
        @endif
    </section>
@endsection
