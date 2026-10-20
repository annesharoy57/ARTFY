
@extends('admin.layout')

@section('title', 'Artwork Review | ARTFY Admin')

@section('page_eyebrow', 'Artfy / Artwork Management')

@section('page_title', 'Artwork Review')

@section('page_intro', 'Review submitted artwork before it appears in the ARTFY public gallery.')

@push('styles')
<style>
    .art-review-messages {
        display: grid;
        gap: 12px;
        margin-bottom: 20px;
    }

    .art-review-message {
        padding: 14px 18px;
        border: 1px solid #c9dfc5;
        border-radius: 10px;
        background: #e2eedf;
        color: #285b31;
        font-size: 14px;
        line-height: 1.7;
    }

    .art-review-message.error {
        border-color: #f0cbc7;
        background: #f8e3e1;
        color: #922f32;
    }

    .art-review-message ul {
        margin: 8px 0 0;
        padding-left: 22px;
    }

    .art-review-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .art-review-stat {
        min-width: 0;
        padding: 20px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--white);
    }

    .art-review-stat-label {
        color: var(--muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .art-review-stat-number {
        display: block;
        margin-top: 9px;
        color: var(--text);
        font-size: 29px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .art-review-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 22px;
    }

    .art-review-filter {
        display: inline-block;
        padding: 10px 15px;
        border: 1px solid var(--border);
        border-radius: 24px;
        background: var(--white);
        color: var(--text);
        font-size: 13px;
        text-decoration: none;
        transition: background .2s, border-color .2s;
    }

    .art-review-filter:hover {
        border-color: var(--olive);
    }

    .art-review-filter.active {
        border-color: var(--olive);
        background: var(--olive);
        color: white;
    }

    .art-review-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        align-items: start;
        gap: 20px;
    }

    .art-review-card {
        min-width: 0;
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 15px;
        background: var(--white);
    }

    .art-review-image {
        display: block;
        width: 100%;
        height: 245px;
        object-fit: cover;
        background: var(--cream);
    }

    .art-review-image-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 245px;
        background: var(--cream);
        color: var(--muted);
        font-size: 13px;
    }

    .art-review-card-content {
        padding: 19px;
    }

    .art-review-card-title {
        margin: 0 0 9px;
        color: var(--text);
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 21px;
        font-weight: 600;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .art-review-artist {
        margin-bottom: 13px;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .art-review-status {
        display: inline-block;
        margin-bottom: 12px;
        padding: 6px 10px;
        border-radius: 22px;
        background: #f1e9cf;
        color: #745d16;
        font-size: 11px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .art-review-status.approved {
        background: #dfeddd;
        color: #426b45;
    }

    .art-review-status.rejected {
        background: #f7dfdd;
        color: #a63d40;
    }

    .art-review-details {
        color: var(--text);
        font-size: 13px;
        line-height: 1.8;
        overflow-wrap: anywhere;
    }

    .art-review-details p {
        margin: 8px 0;
    }

    .art-review-description {
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .art-review-form {
        margin-top: 17px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    .art-review-form label {
        display: block;
        margin-bottom: 8px;
        color: var(--text);
        font-size: 13px;
        font-weight: 700;
    }

    .art-review-form textarea {
        display: block;
        width: 100%;
        min-height: 85px;
        padding: 11px;
        border: 1px solid var(--border);
        border-radius: 9px;
        background: #fffefa;
        color: var(--text);
        font: inherit;
        font-size: 13px;
        line-height: 1.6;
        resize: vertical;
    }

    .art-review-form textarea:focus {
        border-color: var(--olive);
        outline: 2px solid #55613a25;
        outline-offset: 1px;
    }

    .art-review-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
        margin-top: 12px;
    }

    .art-review-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 13px;
        border: 0;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s;
    }

    .art-review-button.approve {
        background: var(--olive);
        color: white;
    }

    .art-review-button.approve:hover {
        background: var(--dark-olive);
    }

    .art-review-button.reject {
        background: #f5dfdc;
        color: #922f32;
    }

    .art-review-button.reject:hover {
        background: #efd0cb;
    }

    .art-review-empty {
        padding: 42px 22px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: var(--white);
        text-align: center;
    }

    .art-review-empty h2 {
        margin: 0 0 10px;
        color: var(--text);
        font-family: 'Playfair Display', Georgia, serif;
        font-size: 23px;
    }

    .art-review-empty p {
        margin: 0;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.8;
    }

    .art-review-pagination {
        margin-top: 24px;
        overflow-x: auto;
    }

    .art-review-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 30px;
        padding-top: 18px;
        border-top: 1px solid var(--border);
        color: var(--muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .art-review-back-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 13px;
        border: 1px solid var(--olive);
        border-radius: 9px;
        background: var(--white);
        color: var(--dark-olive);
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: background .2s, color .2s;
    }

    .art-review-back-link:hover {
        background: var(--olive);
        color: white;
    }

    @media (max-width: 1200px) {
        .art-review-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .art-review-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 750px) {
        .art-review-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .art-review-card-content {
            padding: 15px;
        }

        .art-review-image,
        .art-review-image-placeholder {
            height: 200px;
        }
    }

    @media (max-width: 520px) {
        .art-review-grid {
            grid-template-columns: 1fr;
        }

        .art-review-stat {
            padding: 15px;
        }

        .art-review-stat-number {
            font-size: 25px;
        }

        .art-review-image,
        .art-review-image-placeholder {
            height: 235px;
        }

        .art-review-footer {
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')

    {{-- Session messages and validation errors --}}
    @if (session('success') || session('error') || $errors->any())
        <div class="art-review-messages">

            @if (session('success'))
                <div class="art-review-message">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="art-review-message error">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="art-review-message error">
                    <strong>Please correct the following:</strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    @endif

    {{-- Artwork statistics --}}
    <section class="art-review-stats">

        <div class="art-review-stat">
            <div class="art-review-stat-label">Awaiting review</div>
            <strong class="art-review-stat-number">
                {{ $counts['pending'] }}
            </strong>
        </div>

        <div class="art-review-stat">
            <div class="art-review-stat-label">Approved</div>
            <strong class="art-review-stat-number">
                {{ $counts['approved'] }}
            </strong>
        </div>

        <div class="art-review-stat">
            <div class="art-review-stat-label">Rejected</div>
            <strong class="art-review-stat-number">
                {{ $counts['rejected'] }}
            </strong>
        </div>

        <div class="art-review-stat">
            <div class="art-review-stat-label">Total artworks</div>
            <strong class="art-review-stat-number">
                {{ $counts['all'] }}
            </strong>
        </div>

    </section>

    {{-- Status filters --}}
    <nav class="art-review-filters" aria-label="Filter artworks by moderation status">

        @foreach ([
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'all' => 'All artworks'
        ] as $key => $label)

            <a
                class="art-review-filter {{ $status === $key ? 'active' : '' }}"
                href="{{ route('admin.artworks.index', ['status' => $key]) }}"
                @if ($status === $key) aria-current="page" @endif
            >
                {{ $label }} ({{ $counts[$key] }})
            </a>

        @endforeach

    </nav>

    {{-- Artwork submissions --}}
    @if ($artworks->isEmpty())

        <div class="art-review-empty">
            <h2>No artworks found</h2>
            <p>
                There are no artworks in this review category right now.
            </p>
        </div>

    @else

        <section class="art-review-grid" aria-label="Artwork submissions">

            @foreach ($artworks as $artwork)

                <article class="art-review-card">

                    @if ($artwork->image)
                        <img
                            class="art-review-image"
                            src="{{ asset('storage/' . $artwork->image) }}"
                            alt="{{ $artwork->title }}"
                            loading="lazy"
                        >
                    @else
                        <div class="art-review-image-placeholder">
                            No artwork image available
                        </div>
                    @endif

                    <div class="art-review-card-content">

                        <h2 class="art-review-card-title">
                            {{ $artwork->title }}
                        </h2>

                        <div class="art-review-artist">
                            Artist:
                            {{ $artwork->artist?->artistProfile?->artist_name
                                ?: ($artwork->artist?->name ?? 'Unknown artist') }}
                        </div>

                        <span class="art-review-status {{ $artwork->moderation_status }}">
                            {{ $artwork->moderation_status }}
                        </span>

                        <div class="art-review-details">

                            <p>
                                <strong>Category:</strong>
                                {{ $artwork->category ?: 'Not specified' }}
                            </p>

                            <p>
                                <strong>Medium:</strong>
                                {{ $artwork->medium ?: 'Not specified' }}
                            </p>

                            <p>
                                <strong>Price:</strong>
                                {{ $artwork->price !== null
                                    ? '$' . number_format((float) $artwork->price, 2)
                                    : 'Not specified' }}
                            </p>

                            <p>
                                <strong>Availability:</strong>
                                {{ $artwork->is_sold ? 'Sold' : ($artwork->status ?: 'Not specified') }}
                            </p>

                            <p>
                                <strong>Description:</strong><br>
                                <span class="art-review-description">{{ $artwork->description ?: 'No description provided.' }}</span>
                            </p>

                        </div>

                        @if ($artwork->moderation_status === 'pending')

                            {{-- Approve artwork --}}
                            <div class="art-review-form">

                                <form
                                    method="POST"
                                    action="{{ route('admin.artworks.approve', $artwork) }}"
                                    onsubmit="return confirm('Approve this artwork?');"
                                >
                                    @csrf

                                    <button
                                        class="art-review-button approve"
                                        type="submit"
                                    >
                                        Approve Artwork
                                    </button>
                                </form>

                            </div>

                            {{-- Reject artwork --}}
                            <div class="art-review-form">

                                <form
                                    method="POST"
                                    action="{{ route('admin.artworks.reject', $artwork) }}"
                                    onsubmit="return confirm('Reject this artwork? It will not be deleted.');"
                                >
                                    @csrf

                                    <label for="notes-{{ $artwork->id }}">
                                        Reason for rejection
                                    </label>

                                    <textarea
                                        id="notes-{{ $artwork->id }}"
                                        name="admin_notes"
                                        required
                                        maxlength="2000"
                                        placeholder="Explain what needs to be corrected..."
                                    ></textarea>

                                    <div class="art-review-actions">
                                        <button
                                            class="art-review-button reject"
                                            type="submit"
                                        >
                                            Reject Artwork
                                        </button>
                                    </div>
                                </form>

                            </div>

                        @endif

                    </div>

                </article>

            @endforeach

        </section>

        @if ($artworks->hasPages())
            <div class="art-review-pagination">
                {{ $artworks->links() }}
            </div>
        @endif

    @endif

    <footer class="art-review-footer">
        <span>Artfy Administration · Artwork Review</span>

        <a href="{{ route('home') }}" class="art-review-back-link">
            ← Return to Discover
        </a>
    </footer>

@endsection
