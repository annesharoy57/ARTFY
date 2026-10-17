<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>All Artists | Artfy Admin</title>

    <style>
        :root {
            --paper: #f8f6f2;
            --cream: #efede4;
            --olive: #55613a;
            --dark-olive: #414c2c;
            --text: #28291f;
            --muted: #77786e;
            --white: #fff;
            --border: #e5e1d7;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--paper);
            color: var(--text);
            font-family: Arial, sans-serif;
        }

        .topbar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .brand {
            color: var(--dark-olive);
            font-family: Georgia, serif;
            font-size: 28px;
            font-weight: bold;
            text-decoration: none;
        }

        .back {
            color: var(--olive);
            text-decoration: none;
            font-weight: 600;
        }

        .back:hover {
            color: var(--dark-olive);
        }

        main {
            width: min(1200px, 90%);
            margin: 42px auto;
        }

        .eyebrow {
            color: var(--olive);
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: bold;
        }

        h1 {
            font-family: Georgia, serif;
            font-size: clamp(32px, 5vw, 48px);
            font-weight: normal;
            margin: 10px 0;
        }

        .intro {
            color: var(--muted);
            line-height: 1.7;
            margin-bottom: 28px;
        }

        .summary {
            background: var(--cream);
            padding: 18px 22px;
            border-radius: 12px;
            margin-bottom: 28px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 22px;
        }

        .artist-card {
            display: block;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: transform .2s, box-shadow .2s;
        }

        .artist-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 28px #414c2c12;
        }

        .photo-wrap {
            height: 210px;
            background: var(--cream);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .avatar {
            width: 100px;
            height: 100px;
            flex-shrink: 0;
            border-radius: 50%;
            place-items: center;
            background: var(--olive);
            color: white;
            font-family: Georgia, serif;
            font-size: 38px;
        }

        .card-body {
            padding: 18px;
        }

        .artist-name {
            font-family: Georgia, serif;
            font-size: 21px;
            margin: 0 0 9px;
            font-weight: normal;
            overflow-wrap: anywhere;
        }

        .detail {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
            margin: 5px 0;
            overflow-wrap: anywhere;
        }

        .status {
            display: inline-block;
            background: var(--cream);
            color: var(--dark-olive);
            padding: 6px 10px;
            border-radius: 30px;
            font-size: 11px;
            line-height: 1.5;
            margin-top: 10px;
        }

        .empty {
            background: white;
            border: 1px solid var(--border);
            padding: 35px;
            border-radius: 12px;
            color: var(--muted);
        }

        .pagination {
            margin-top: 28px;
        }

        @media (max-width: 520px) {
            main {
                margin: 28px auto;
            }

            .photo-wrap {
                height: 180px;
            }

            .topbar {
                padding: 16px 5%;
            }

            .brand {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <header class="topbar">
        <a href="{{ route('admin.dashboard') }}" class="brand">
            ARTFY
        </a>

        <a href="{{ route('admin.dashboard') }}" class="back">
            ← Admin Dashboard
        </a>
    </header>

    <main>
        <div class="eyebrow">Community directory</div>

        <h1>All Artists</h1>

        <p class="intro">
            Meet the artists who have joined Artfy. Open an artist's profile
            to inspect their profile information and uploaded artwork.
        </p>

        <div class="summary">
            <strong>{{ $artists->total() }}</strong>
            {{ $artists->total() === 1 ? 'artist account' : 'artist accounts' }}
            registered
        </div>

        @if ($artists->isEmpty())
            <div class="empty">
                No artist accounts found yet.
            </div>
        @else
            <div class="grid">
                @foreach ($artists as $artist)
                    @php
                        $profile = $artist->artistProfile;
                        $application = $artist->artistApplication;

                        $artistName = $profile?->artist_name ?: $artist->name;

                        $photo = $profile?->profile_photo;

                        // Build the URL from the public storage disk.
                        $photoUrl = $photo
                            ? asset('storage/' . ltrim($photo, '/'))
                            : null;

                        $membershipApproved =
                            $application?->status === 'approved'
                            && $application?->payment_status === 'verified';

                        $initial = mb_strtoupper(
                            mb_substr($artistName, 0, 1)
                        );
                    @endphp

                    <a
                        class="artist-card"
                        href="{{ route('admin.artists.show', ['user' => $artist->id]) }}"
                    >
                        <div class="photo-wrap">
                            @if ($photoUrl)
                                <img
                                    class="photo"
                                    src="{{ $photoUrl }}"
                                    alt="{{ $artistName }} profile photo"
                                    loading="lazy"
                                    onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='grid';"
                                >

                                <div
                                    class="avatar"
                                    style="display: none;"
                                    aria-label="{{ $artistName }} initials"
                                >
                                    {{ $initial }}
                                </div>
                            @else
                                <div
                                    class="avatar"
                                    style="display: grid;"
                                    aria-label="{{ $artistName }} initials"
                                >
                                    {{ $initial }}
                                </div>
                            @endif
                        </div>

                        <div class="card-body">
                            <h2 class="artist-name">
                                {{ $artistName }}
                            </h2>

                            <p class="detail">
                                {{ $profile?->art_category
                                    ?: $application?->art_category
                                    ?: 'Independent artist' }}
                            </p>

                            <p class="detail">
                                {{ $profile?->location ?: 'Location not added' }}
                            </p>

                            <span class="status">
                                @if ($membershipApproved)
                                    Approved artist
                                @else
                                    Membership:
                                    {{ ucfirst($application?->status ?? 'not submitted') }}

                                    @if ($application?->status === 'approved')
                                        · Payment:
                                        {{ ucfirst($application?->payment_status ?? 'pending') }}
                                    @endif
                                @endif
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="pagination">
                {{ $artists->links() }}
            </div>
        @endif
    </main>
</body>
</html>
