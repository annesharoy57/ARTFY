@php
    $imageUrl = $resolveArtfyImage($item['image'] ?? null);
@endphp
<article class="ag-discovery-card">
    <a class="ag-discovery-card-link" href="{{ route('artworks.show', $item['slug']) }}">
    <div class="ag-discovery-art-image">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $item['title'] ?? 'Artwork' }}" loading="lazy">
        @else
            <div class="ag-discovery-artist-fallback" aria-hidden="true">✳</div>
        @endif
    </div>
    <div class="ag-discovery-card-copy">
        <strong>{{ $item['title'] ?? 'Untitled artwork' }}</strong>
        <span>by {{ $item['artist'] ?? 'Artfy Artist' }}</span>
        <small>♡ {{ number_format((int) ($item['likes'] ?? 0)) }} {{ (int) ($item['likes'] ?? 0) === 1 ? 'like' : 'likes' }}</small>
    </div>
    </a>

    @auth
        @if (auth()->user()->isArtLover())
            <form class="ag-discovery-save" action="{{ route('artworks.favorite', $item['id']) }}" method="POST">
                @csrf
                <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
                <button class="ag-save-button" type="submit" aria-label="{{ $item['is_saved'] ? 'Remove from favorites' : 'Save to favorites' }}" aria-pressed="{{ $item['is_saved'] ? 'true' : 'false' }}">
                    <svg class="artwork-save-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4.5A2.5 2.5 0 0 1 8.5 2h7A2.5 2.5 0 0 1 18 4.5V22l-6-4-6 4V4.5Z"/></svg>
                </button>
            </form>
        @endif
    @else
        <a class="ag-discovery-save ag-save-button" href="{{ route('login', ['redirect' => url()->current()]) }}" aria-label="Log in to save to favorites">
            <svg class="artwork-save-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4.5A2.5 2.5 0 0 1 8.5 2h7A2.5 2.5 0 0 1 18 4.5V22l-6-4-6 4V4.5Z"/></svg>
        </a>
    @endauth
</article>
