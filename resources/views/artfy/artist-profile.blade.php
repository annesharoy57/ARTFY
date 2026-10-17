@extends('artfy.layout')

@section('content')
<div class="artist-profile-page">
    <div class="profile-heading">
        <p class="profile-eyebrow">YOUR CREATIVE SPACE</p>
        <h1>My Artist Profile</h1>
        <p>Tell the world about your art, your story, and your creative journey.</p>
    </div>

    @if (session('success'))
        <div class="profile-message success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="profile-message error">
            <strong>Please check the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('artist-profile.update') }}"
          method="POST"
          enctype="multipart/form-data"
          class="artist-profile-form">
        @csrf
        @method('PUT')

        <section class="profile-card">
            <h2>Profile photographs</h2>
            <p class="profile-help">Add a cover image and a profile picture to introduce yourself.</p>

            @if ($profile?->cover_photo)
                <img
                    src="{{ asset('storage/' . $profile->cover_photo) }}"
                    alt="Current cover photo"
                    class="cover-preview">
            @endif

            <label for="cover_photo">Cover photo</label>
            <input type="file" id="cover_photo" name="cover_photo" accept="image/*">
            <small>JPG, PNG or WebP; maximum 5 MB.</small>

            @if ($profile?->profile_photo)
                <img
                    src="{{ asset('storage/' . $profile->profile_photo) }}"
                    alt="Current profile photo"
                    class="avatar-preview">
            @endif

            <label for="profile_photo">Profile photo</label>
            <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
            <small>JPG, PNG or WebP; maximum 2 MB.</small>
        </section>

        <section class="profile-card">
            <h2>About the artist</h2>

            <label for="artist_name">Artist name *</label>
            <input
                type="text"
                id="artist_name"
                name="artist_name"
                value="{{ old('artist_name', $profile?->artist_name ?? $user->name) }}"
                maxlength="255"
                required>

            <label for="bio">Biography</label>
            <textarea
                id="bio"
                name="bio"
                rows="5"
                maxlength="2000"
                placeholder="Tell visitors about yourself and your artistic journey...">{{ old('bio', $profile?->bio) }}</textarea>

            <label for="art_category">Art category</label>
            <input
                type="text"
                id="art_category"
                name="art_category"
                value="{{ old('art_category', $profile?->art_category) }}"
                maxlength="255"
                placeholder="e.g. Painting, Digital Art, Photography">

            <label for="location">Location</label>
            <input
                type="text"
                id="location"
                name="location"
                value="{{ old('location', $profile?->location) }}"
                maxlength="255"
                placeholder="City or country">

            <label for="portfolio_url">Portfolio website</label>
            <input
                type="url"
                id="portfolio_url"
                name="portfolio_url"
                value="{{ old('portfolio_url', $profile?->portfolio_url) }}"
                maxlength="255"
                placeholder="https://example.com">
        </section>

        <button type="submit" class="profile-save-button">
            Save Profile
        </button>
    </form>
</div>

<style>
    .artist-profile-page {
        max-width: 900px;
        margin: 0 auto;
        padding: 48px 24px;
        color: #414c2c;
    }

    .profile-heading {
        margin-bottom: 28px;
    }

    .profile-eyebrow {
        color: #72795f;
        font-size: 12px;
        letter-spacing: 3px;
        font-weight: 700;
    }

    .profile-heading h1 {
        font-size: clamp(30px, 5vw, 46px);
        margin: 10px 0;
    }

    .profile-heading > p:last-child,
    .profile-help {
        color: #72795f;
        line-height: 1.7;
    }

    .profile-card {
        background: #fffef9;
        border: 1px solid #e1e2d5;
        border-radius: 18px;
        padding: 28px;
        margin-bottom: 22px;
    }

    .profile-card h2 {
        margin-top: 0;
        font-size: 22px;
    }

    .artist-profile-form label {
        display: block;
        margin: 20px 0 8px;
        font-weight: 600;
    }

    .artist-profile-form input:not([type="file"]),
    .artist-profile-form textarea {
        display: block;
        width: 100%;
        box-sizing: border-box;
        padding: 13px 14px;
        border: 1px solid #d8dacb;
        border-radius: 9px;
        background: #f5f3e8;
        color: #414c2c;
        font: inherit;
    }

    .artist-profile-form input[type="file"] {
        display: block;
        max-width: 100%;
        margin: 8px 0;
    }

    .profile-card small {
        display: block;
        color: #72795f;
        margin-top: 5px;
    }

    .cover-preview {
        display: block;
        width: 100%;
        max-height: 240px;
        object-fit: cover;
        border-radius: 12px;
        margin: 12px 0;
    }

    .avatar-preview {
        display: block;
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        margin-top: 22px;
    }

    .profile-save-button {
        border: 0;
        border-radius: 9px;
        padding: 14px 28px;
        background: #55613a;
        color: white;
        font: inherit;
        font-weight: 700;
        cursor: pointer;
    }

    .profile-save-button:hover {
        background: #414c2c;
    }

    .profile-message {
        padding: 15px 18px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .profile-message ul {
        margin-bottom: 0;
    }

    .profile-message.success {
        background: #e8efe0;
        color: #314728;
    }

    .profile-message.error {
        background: #f8e8e5;
        color: #8a3025;
    }

    @media (max-width: 600px) {
        .artist-profile-page {
            padding: 28px 16px;
        }

        .profile-card {
            padding: 20px;
        }
    }
</style>
@endsection
