<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Artist Preview | Artfy Admin')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="{{ asset('css/artfy.css') }}">

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
            background: #f8f6f2;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f8f6f2;
            color: #292b23;
            font-family: 'DM Sans', sans-serif;
        }

        .admin-artist-preview {
            min-height: 100vh;
            padding: 1px 0 50px;
        }

        /* Simple admin header — no public navigation */
        .admin-preview-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 16px 28px;
            background: #ffffff;
            border-bottom: 1px solid #e5e2d8;
        }

        .admin-preview-brand {
            color: #424c31;
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 25px;
            font-weight: 600;
            letter-spacing: 1px;
            text-decoration: none;
        }

        .admin-preview-label {
            color: #77796f;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-align: center;
            text-transform: uppercase;
        }

        .admin-preview-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 16px;
            border: 1px solid #c9c9b9;
            border-radius: 4px;
            background: #ffffff;
            color: #424a32;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .admin-preview-back:hover {
            background: #eeede5;
        }

        /* Artist profile container */
        .admin-artist-preview .artist-profile-page {
            width: 100%;
            max-width: 1240px;
            margin: 0 auto;
            padding: 28px 24px 60px;
            color: #292b23;
        }

        /* Keep uploaded images properly sized */
        .admin-artist-preview .artist-cover img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .admin-artist-preview .artist-avatar img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .admin-artist-preview .artist-artwork-image img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Hide editing/upload controls in the admin preview.
           The profile's own admin-preview condition also hides them. */
        .admin-artist-preview .artist-profile-actions {
            align-items: center;
        }

        .admin-artist-preview .artist-artwork-card {
            cursor: pointer;
        }

        @media (max-width: 700px) {
            .admin-preview-topbar {
                flex-wrap: wrap;
                padding: 15px 18px;
            }

            .admin-preview-label {
                order: 3;
                width: 100%;
                text-align: left;
            }

            .admin-artist-preview .artist-profile-page {
                padding: 20px 16px 45px;
            }
        }

        @media (max-width: 420px) {
            .admin-preview-brand {
                font-size: 22px;
            }

            .admin-preview-back {
                padding: 9px 11px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="admin-artist-preview">

        <header class="admin-preview-topbar">

            <a
                class="admin-preview-brand"
                href="{{ route('admin.dashboard') }}"
            >
                ARTFY
            </a>

            <span class="admin-preview-label">
                Administrator · Artist Preview
            </span>

            <a
                class="admin-preview-back"
                href="{{ route('admin.artists.index') }}"
            >
                &larr; Back to Artists
            </a>

        </header>

        <main>
            @yield('content')
        </main>

    </div>
</body>
</html>
