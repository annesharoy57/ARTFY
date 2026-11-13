@php
    $imageUrl = $resolveArtfyImage($item['image'] ?? null);
    $artistName = $item['name'] ?? 'Artfy Artist';
@endphp
<a class="ag-discovery-card" href="{{ route('artists.show', $item['slug']) }}">
    <div class="ag-discovery-artist-image">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="Portrait of {{ $artistName }}" loading="lazy">
        @else
            <div class="ag-discovery-artist-fallback" aria-hidden="true">{{ mb_strtoupper(mb_substr($artistName, 0, 1)) }}</div>
        @endif
    </div>
    <div class="ag-discovery-card-copy">
        <strong>{{ $artistName }}</strong>
        <span>{{ $item['discipline'] ?? 'Independent artist' }}</span>
        <small>♧ {{ number_format((int) ($item['follower_count'] ?? 0)) }} {{ (int) ($item['follower_count'] ?? 0) === 1 ? 'follower' : 'followers' }}</small>
    </div>
</a>
