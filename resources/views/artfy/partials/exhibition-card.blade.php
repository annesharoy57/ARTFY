<a
    class="ag-exhibition-card {{ $featured ? 'is-ongoing-featured' : '' }}"
    style="--exhibition-accent: {{ $item['accent_color'] ?? '#55613a' }}"
    href="{{ route('exhibitions.show', $item['slug']) }}"
>
    <div class="ag-exhibition-artwork">
        @if (!empty($item['cover_image']))
            <img src="{{ asset('storage/' . $item['cover_image']) }}" alt="{{ $item['title'] }} exhibition poster" loading="lazy">
        @else
            <div class="ag-exhibition-poster-fallback" aria-hidden="true">
                <span>ARTFY · ONLINE EXHIBITION</span>
                <strong>{{ $item['title'] }}</strong>
                @if (!empty($item['theme']))<small>{{ $item['theme'] }}</small>@endif
            </div>
        @endif
        <span class="ag-exhibition-status">{{ $item['exhibition_state'] }}</span>
    </div>
    <div class="ag-exhibition-card-copy">
        @if ($featured)<span class="ag-featured-kicker">NOW SHOWING</span>@endif
        <strong>{{ $item['title'] }}</strong>
        @if (!empty($item['theme']))<em>{{ $item['theme'] }}</em>@endif
        <small>{{ $item['subtitle'] ?? '' }}</small>
        @if ($featured)<span class="ag-exhibition-cta">Explore the exhibition <span aria-hidden="true">→</span></span>@endif
    </div>
</a>
