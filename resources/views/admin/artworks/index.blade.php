<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artwork Review | ARTFY Admin</title>

    <style>
        :root {
            --cream: #f5f3e8;
            --olive: #55613a;
            --dark-olive: #414c2c;
            --muted: #72795f;
            --white: #fffefa;
            --border: #e4e2d5;
            --red: #a63d40;
            --green: #426b45;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--cream);
            color: #292d23;
            font-family: Arial, sans-serif;
        }

        header {
            background: var(--dark-olive);
            color: white;
            padding: 24px 5%;
        }

        header h1 {
            margin: 0 0 8px;
            font-size: 27px;
        }

        header p {
            margin: 0;
            color: #e2e5d8;
        }

        main {
            max-width: 1250px;
            margin: 30px auto;
            padding: 0 20px;
        }

        a {
            color: var(--olive);
            text-decoration: none;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .message {
            padding: 14px 18px;
            margin-bottom: 18px;
            border-radius: 9px;
            background: #e2eedf;
            color: #285b31;
        }

        .error {
            background: #f8e3e1;
            color: #922f32;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 25px;
        }

        .stat {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 13px;
            padding: 20px;
        }

        .stat span {
            color: var(--muted);
            font-size: 13px;
        }

        .stat strong {
            display: block;
            font-size: 28px;
            margin-top: 8px;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 22px;
        }

        .filter {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 20px;
            background: var(--white);
            border: 1px solid var(--border);
            color: #424638;
        }

        .filter.active {
            background: var(--olive);
            color: white;
            border-color: var(--olive);
        }

        .artwork-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
        }

        .artwork-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 15px;
            overflow: hidden;
        }

        .artwork-image {
            width: 100%;
            height: 245px;
            object-fit: cover;
            display: block;
            background: #e9e6da;
        }

        .artwork-content {
            padding: 19px;
        }

        .artwork-content h2 {
            margin: 0 0 9px;
            font-size: 20px;
        }

        .artist {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 12px;
        }

        .details {
            font-size: 14px;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            background: #f1e9cf;
            color: #745d16;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status.approved {
            background: #dfeddd;
            color: var(--green);
        }

        .status.rejected {
            background: #f7dfdd;
            color: var(--red);
        }

        .review-form {
            margin-top: 18px;
            border-top: 1px solid var(--border);
            padding-top: 16px;
        }

        .review-form label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .review-form textarea {
            width: 100%;
            min-height: 75px;
            padding: 10px;
            font: inherit;
            border: 1px solid var(--border);
            border-radius: 8px;
            resize: vertical;
        }

        .actions {
            display: flex;
            gap: 9px;
            margin-top: 12px;
        }

        button {
            border: 0;
            border-radius: 8px;
            padding: 10px 13px;
            font-weight: bold;
            cursor: pointer;
        }

        .approve-btn {
            background: var(--olive);
            color: white;
        }

        .reject-btn {
            background: #f5dfdc;
            color: #922f32;
        }

        .empty {
            background: var(--white);
            border: 1px solid var(--border);
            padding: 40px 20px;
            text-align: center;
            border-radius: 14px;
        }

        .pagination {
            margin: 25px 0;
        }

        @media (max-width: 950px) {
            .artwork-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .artwork-grid {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .artwork-image {
                height: 220px;
            }
        }
    </style>
</head>

<body>
<header>
    <h1>Artwork Review</h1>
    <p>Review submitted artwork before it appears in the ARTFY public gallery.</p>
</header>

<main>
    <a class="back-link" href="{{ route('admin.dashboard') }}">
        &larr; Back to Admin Dashboard
    </a>

    @if (session('success'))
        <div class="message">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="message error">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="message error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="stats">
        <div class="stat">
            <span>Awaiting review</span>
            <strong>{{ $counts['pending'] }}</strong>
        </div>

        <div class="stat">
            <span>Approved</span>
            <strong>{{ $counts['approved'] }}</strong>
        </div>

        <div class="stat">
            <span>Rejected</span>
            <strong>{{ $counts['rejected'] }}</strong>
        </div>

        <div class="stat">
            <span>Total artworks</span>
            <strong>{{ $counts['all'] }}</strong>
        </div>
    </section>

    <nav class="filters">
        @foreach ([
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'all' => 'All artworks'
        ] as $key => $label)
            <a
                class="filter {{ $status === $key ? 'active' : '' }}"
                href="{{ route('admin.artworks.index', ['status' => $key]) }}"
            >
                {{ $label }} ({{ $counts[$key] }})
            </a>
        @endforeach
    </nav>

    @if ($artworks->isEmpty())
        <div class="empty">
            <h2>No artworks found</h2>
            <p>There are no artworks in this review category right now.</p>
        </div>
    @else
        <section class="artwork-grid">
            @foreach ($artworks as $artwork)
                <article class="artwork-card">

                    <img
                        class="artwork-image"
                        src="{{ asset('storage/' . $artwork->image) }}"
                        alt="{{ $artwork->title }}"
                        loading="lazy"
                    >

                    <div class="artwork-content">
                        <h2>{{ $artwork->title }}</h2>

                        <div class="artist">
                            Artist:
                            {{ $artwork->artist?->artistProfile?->artist_name ?: ($artwork->artist?->name ?? 'Unknown artist') }}
                        </div>

                        <span class="status {{ $artwork->moderation_status }}">
                            {{ $artwork->moderation_status }}
                        </span>

                        <div class="details">
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
                                {{ $artwork->price !== null ? '$' . number_format((float) $artwork->price, 2) : 'Not specified' }}
                            </p>

                            <p>
                                <strong>Availability:</strong>
                                {{ $artwork->is_sold ? 'Sold' : $artwork->status }}
                            </p>

                            <p>
                                <strong>Description:</strong><br>
                                {{ $artwork->description ?: 'No description provided.' }}
                            </p>
                        </div>

                        @if ($artwork->moderation_status === 'pending')
                            <div class="review-form">
                                <form
                                    method="POST"
                                    action="{{ route('admin.artworks.approve', $artwork) }}"
                                    onsubmit="return confirm('Approve this artwork?');"
                                >
                                    @csrf
                                    <button class="approve-btn" type="submit">
                                        Approve Artwork
                                    </button>
                                </form>

                                <form
                                    class="review-form"
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

                                    <div class="actions">
                                        <button class="reject-btn" type="submit">
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

        <div class="pagination">
            {{ $artworks->links() }}
        </div>
    @endif
</main>
</body>
</html>
