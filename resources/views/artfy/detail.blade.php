@extends('artfy.layout')

@section('title', $title)

@section('content')
    <section class="detail-page">
        <a class="back-link" href="{{ match ($pageType) {'artwork' => route('artworks.index'), 'artist' => route('artists.index'), default => route('exhibitions.index')} }}">← Back to {{ $pageType === 'artwork' ? 'artworks' : ($pageType === 'artist' ? 'artists' : 'exhibitions') }}</a>
        <div class="detail-layout">
            <div class="detail-art artwork-art {{ $item['color'] }}"><span class="detail-stamp">{{ $eyebrow }}</span></div>
            <div class="detail-copy">
                <span class="eyebrow">{{ $eyebrow }}</span><h1>{{ $title }}</h1><p class="detail-description">{{ $description }}</p>
                @if ($pageType === 'artwork')
                    <div class="detail-facts"><span>Artist<a href="{{ route('artists.show', $item['artist_slug']) }}">{{ $item['artist'] }} ↗</a></span><span>Category<strong>{{ $item['category'] }}</strong></span><span>Medium<strong>{{ $item['type'] }}</strong></span></div>
                    <div class="detail-actions"><span class="detail-price">${{ $item['price'] }}</span><a class="button button-dark" href="{{ route('favorites.index') }}">Save to favorites <span>♡</span></a></div>
                @elseif ($pageType === 'artist')
                    <div class="detail-facts"><span>Based in<strong>{{ $item['discipline'] }}</strong></span></div>
                    <a class="button button-dark" href="{{ route('artworks.index', ['q' => $item['name']]) }}">Explore {{ $item['name'] }}’s work <span>→</span></a>
                @else
                    <a class="button button-dark" href="{{ route('artworks.index') }}">Explore artworks <span>→</span></a>
                @endif
            </div>
        </div>
    </section>
@endsection
