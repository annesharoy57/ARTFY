@extends('artfy.layout')

@section('title', $title)

@section('content')
    <section class="page-intro">
        <span class="eyebrow">{{ $eyebrow }}</span>
        <h1>{{ $title }}</h1>
        <p>{{ $description }}</p>
        @if ($pageType === 'artworks')
            <form class="filter-bar" action="{{ route('artworks.index') }}" method="GET">
                <label class="search-field"><span aria-hidden="true">⌕</span><span class="visually-hidden">Search</span><input type="search" name="q" value="{{ $query }}" placeholder="Search artworks or artists"></label>
                <label><span class="visually-hidden">Category</span><select name="category"><option value="">All categories</option>@foreach (['Painting', 'Photography', 'Illustration'] as $option)<option value="{{ $option }}" @selected($category === $option)>{{ $option }}</option>@endforeach</select></label>
                <label><span class="visually-hidden">Art type</span><select name="type"><option value="">All types</option>@foreach (['Acrylic', 'Digital', 'Oil'] as $option)<option value="{{ $option }}" @selected($type === $option)>{{ $option }}</option>@endforeach</select></label>
                <label><span class="visually-hidden">Sort artworks</span><select name="sort"><option value="latest" @selected($sort === 'latest')>Latest</option><option value="popular" @selected($sort === 'popular')>Most popular</option><option value="price-low" @selected($sort === 'price-low')>Price: low to high</option></select></label>
                <button class="button button-dark button-small" type="submit">Apply filters</button>
            </form>
        @endif
    </section>

    <section class="section listing-section">
        @if (count($items) === 0)
            <div class="empty-state"><span>✳</span><h2>No matches just yet</h2><p>Try a different search or clear one of your filters.</p><a class="button button-dark" href="{{ route('artworks.index') }}">See all artworks</a></div>
        @elseif ($pageType === 'artworks')
            <div class="card-grid artwork-grid">
                @foreach ($items as $item)
                    <a class="art-card" href="{{ route('artworks.show', $item['slug']) }}">
                        <div class="art-card-image artwork-art {{ $item['color'] }}"><span class="art-card-category">{{ $item['category'] }}</span><span class="like-count">♡ {{ $item['likes'] }}</span></div>
                        <div class="art-card-info"><div><h3>{{ $item['title'] }}</h3><p>by <span>{{ $item['artist'] }}</span></p></div><strong>${{ $item['price'] }}</strong></div>
                    </a>
                @endforeach
            </div>
        @elseif ($pageType === 'artists')
            <div class="artist-grid artist-list">
                @foreach ($items as $item)
                    <a class="artist-card" href="{{ route('artists.show', $item['slug']) }}">
                        <span class="artist-avatar artwork-art {{ $item['color'] }}">{{ strtoupper(substr($item['name'], 0, 1)) }}</span>
                        <span class="artist-card-name">{{ $item['name'] }}</span><span class="artist-card-meta">{{ $item['discipline'] }}</span>
                        <span class="artist-card-arrow">↗</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="exhibition-grid">
                @foreach ($items as $item)
                    <a class="exhibition-card artwork-art {{ $item['color'] }}" href="{{ route('exhibitions.show', $item['slug']) }}">
                        <span class="preview-tag">Online exhibition</span><strong>{{ $item['title'] }}</strong><small>{{ $item['subtitle'] }}</small><span class="preview-arrow">↗</span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
