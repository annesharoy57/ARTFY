@extends('artfy.layout')

@section('title', 'My Artist Profile | Artfy')

@section('content')
<div class="artist-profile-page">
    @php
        $displayArtistName = old('artist_name', $profile?->artist_name ?? $user->name);
    @endphp

    <section class="profile-heading">
        <div class="profile-heading-copy">
            <p class="profile-eyebrow">ARTIST STUDIO / PROFILE</p>
            <h1>Make your profile<br><em>feel like you.</em></h1>
            <p>Introduce your work, your story, and the place it ships from.</p>
        </div>
        <div class="profile-heading-identity">
            @if ($profile?->profile_photo)
                <img src="{{ asset('storage/' . $profile->profile_photo) }}" alt="{{ $displayArtistName }}" class="heading-avatar">
            @else
                <span class="heading-avatar heading-avatar-fallback">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($displayArtistName, 0, 1)) }}</span>
            @endif
            <span><small>YOUR PUBLIC NAME</small><strong>{{ $displayArtistName }}</strong><em>Visible to the Artfy community</em></span>
        </div>
        <span class="profile-hero-orbit orbit-one" aria-hidden="true"></span>
        <span class="profile-hero-orbit orbit-two" aria-hidden="true"></span>
    </section>

    @if (session('success'))
        <div class="profile-message success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="profile-message error">{{ session('error') }}</div>
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

    <form
        action="{{ route('artist-profile.update') }}"
        method="POST"
        enctype="multipart/form-data"
        class="artist-profile-form"
        id="artist-profile-form"
    >
        @csrf
        @method('PUT')

        <div class="profile-form-grid">
            <section class="profile-card photo-card">
                <div class="profile-section-heading"><span class="section-icon">&#10022;</span><div><small>VISUAL IDENTITY</small><h2>Your photographs</h2></div></div>
                <p class="profile-help">A cover image and portrait help collectors recognize your work.</p>

                <div class="photo-upload-block">
                    <div class="cover-preview-frame">
                        <img id="cover-photo-preview" class="cover-preview" src="{{ $profile?->cover_photo ? asset('storage/' . $profile->cover_photo) : '' }}" alt="Cover photo preview" @if (! $profile?->cover_photo) hidden @endif>
                        <span id="cover-photo-placeholder" class="photo-placeholder" @if ($profile?->cover_photo) hidden @endif><i aria-hidden="true">▱</i><strong>Cover image</strong><small>Wide format works best</small></span>
                        <span class="preview-caption">PROFILE COVER</span>
                    </div>
                    <label for="cover_photo">Change cover photo</label>
                    <input type="file" id="cover_photo" name="cover_photo" accept="image/jpeg,image/png,image/webp">
                    <small class="upload-help">JPG, PNG or WebP · Maximum 5 MB</small>
                </div>

                <div class="photo-upload-block avatar-upload-block">
                    <div class="avatar-preview-frame">
                        <img id="profile-photo-preview" class="avatar-preview" src="{{ $profile?->profile_photo ? asset('storage/' . $profile->profile_photo) : '' }}" alt="Profile photo preview" @if (! $profile?->profile_photo) hidden @endif>
                        <span id="profile-photo-placeholder" class="photo-placeholder" @if ($profile?->profile_photo) hidden @endif><i aria-hidden="true">◎</i><strong>Artist portrait</strong><small>Add a clear portrait</small></span>
                    </div>
                    <label for="profile_photo">Profile photo</label>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/webp">
                    <small class="upload-help">JPG, PNG or WebP · Maximum 2 MB</small>
                </div>
            </section>

            <section class="profile-card about-card">
                <div class="profile-section-heading"><span class="section-icon">&#10022;</span><div><small>YOUR STORY</small><h2>About the artist</h2></div></div>
                <p class="profile-help">These details appear on your artist profile and alongside your work.</p>
                <div class="profile-fields-grid">
                    <div class="profile-field"><label for="artist_name">Artist name <b>*</b></label><input type="text" id="artist_name" name="artist_name" value="{{ old('artist_name', $profile?->artist_name ?? $user->name) }}" maxlength="255" required placeholder="How collectors know you"></div>
                    <div class="profile-field"><label for="art_category">Art category</label><input type="text" id="art_category" name="art_category" value="{{ old('art_category', $profile?->art_category) }}" maxlength="255" placeholder="Painting, photography..."></div>
                    <div class="profile-field full-field"><label for="bio">Biography</label><textarea id="bio" name="bio" rows="5" maxlength="2000" placeholder="Tell visitors about yourself and your artistic journey...">{{ old('bio', $profile?->bio) }}</textarea><small>Share your background, materials, inspiration, and the ideas behind your work.</small></div>
                    <div class="profile-field full-field"><label for="portfolio_url">Portfolio website</label><input type="url" id="portfolio_url" name="portfolio_url" value="{{ old('portfolio_url', $profile?->portfolio_url) }}" maxlength="255" placeholder="https://example.com"></div>
                </div>
            </section>
        </div>

        <section class="profile-card dispatch-card">
            <div class="profile-section-heading"><span class="section-icon map-section-icon">&#8974;</span><div><small>DELIVERY DETAILS</small><h2>Choose your dispatch location</h2></div></div>
            <p class="profile-help">Set the place from which your artwork ships so delivery estimates can be calculated more accurately.</p>
            <div class="dispatch-address-row">
                <div class="profile-field"><label for="location">Location / dispatch address</label><input type="text" id="location" name="location" value="{{ old('location', $profile?->location) }}" maxlength="255" placeholder="Road, area, city, Bangladesh" autocomplete="street-address"></div>
                <div class="location-help"><strong>Pinpoint your location</strong><p>Search for a street or landmark, then adjust the map marker to your dispatch point.</p></div>
            </div>
            <div class="map-search-row">
                <button type="button" id="search-location-button" class="map-action-button"><span aria-hidden="true">&#8981;</span> Search address</button>
                <button type="button" id="clear-location-button" class="map-secondary-button">Clear selection</button>
            </div>
            <p id="map-status" class="map-status" role="status" aria-live="polite">Loading map...</p>
            <div id="artist-dispatch-map" role="application" aria-label="Select your artwork dispatch location"></div>
            <div id="map-search-results" class="map-search-results"></div>
            <p class="map-instructions"><strong>Tip:</strong> Zoom in and place the marker at your actual dispatch point. A search result may identify a nearby road or landmark rather than your exact address.</p>
            <div class="coordinate-grid">
                <div><label for="latitude">Latitude</label><input type="number" id="latitude" name="latitude" value="{{ old('latitude', $profile?->latitude) }}" min="-90" max="90" step="any" placeholder="Filled from map"></div>
                <div><label for="longitude">Longitude</label><input type="number" id="longitude" name="longitude" value="{{ old('longitude', $profile?->longitude) }}" min="-180" max="180" step="any" placeholder="Filled from map"></div>
            </div>
            <small class="coordinate-help">Coordinates are optional while editing, but if you provide them, enter both. Choose a location inside Bangladesh.</small>
        </section>

        <div class="profile-savebar"><span>Your profile changes are saved when you choose Save profile.</span><button type="submit" class="profile-save-button">Save profile <span aria-hidden="true">&rarr;</span></button></div>
    </form>
</div>

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
>

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

    .artist-profile-form input:focus,
    .artist-profile-form textarea:focus {
        outline: 2px solid #72795f;
        outline-offset: 2px;
    }

    .profile-card small {
        display: block;
        color: #72795f;
        margin-top: 5px;
        line-height: 1.6;
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

    .location-help {
        margin-top: 24px;
        padding: 16px 18px;
        border-radius: 10px;
        background: #f5f3e8;
        border-left: 4px solid #72795f;
    }

    .location-help strong {
        display: block;
        margin-bottom: 6px;
    }

    .location-help p {
        margin: 0;
        color: #72795f;
        line-height: 1.7;
        font-size: 14px;
    }

    .map-search-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 16px 0 10px;
    }

    .map-action-button,
    .map-secondary-button {
        border: 0;
        border-radius: 8px;
        padding: 12px 18px;
        font: inherit;
        font-weight: 600;
        cursor: pointer;
    }

    .map-action-button {
        background: #55613a;
        color: white;
    }

    .map-action-button:hover {
        background: #414c2c;
    }

    .map-action-button:disabled {
        opacity: .65;
        cursor: wait;
    }

    .map-secondary-button {
        background: #e9e8dc;
        color: #414c2c;
    }

    .map-secondary-button:hover {
        background: #dcdccc;
    }

    .map-status {
        margin: 12px 0;
        color: #55613a;
        font-size: 14px;
        line-height: 1.6;
    }

    #artist-dispatch-map {
        width: 100%;
        height: 400px;
        min-height: 300px;
        border: 1px solid #d8dacb;
        border-radius: 12px;
        overflow: hidden;
        background: #eee;
        z-index: 1;
    }

    .map-instructions {
        margin: 12px 0 18px;
        color: #72795f;
        font-size: 13px;
        line-height: 1.7;
    }

    .map-search-results {
        display: grid;
        gap: 8px;
        margin-top: 12px;
    }

    .map-result-button {
        display: block;
        width: 100%;
        padding: 12px;
        border: 1px solid #d8dacb;
        border-radius: 8px;
        background: white;
        color: #414c2c;
        text-align: left;
        font: inherit;
        cursor: pointer;
    }

    .map-result-button:hover {
        background: #f5f3e8;
        border-color: #72795f;
    }

    .map-result-type {
        display: block;
        margin-top: 4px;
        color: #72795f;
        font-size: 12px;
    }

    .coordinate-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .coordinate-grid label {
        margin-top: 16px;
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
        transition: background .2s ease;
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

        .coordinate-grid {
            grid-template-columns: 1fr;
            gap: 0;
        }

        #artist-dispatch-map {
            height: 320px;
        }
    }

    .cover-preview[hidden],.avatar-preview[hidden],.photo-placeholder[hidden]{display:none!important}
    .artist-profile-page{max-width:1120px;padding:38px 26px 70px;color:#35412d}
    .profile-heading{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:24px;min-height:220px;margin:0 0 20px;padding:32px 37px;border:1px solid rgba(255,255,255,.16);border-radius:23px;background:linear-gradient(112deg,#414c2c,#596641 72%,#6e7952);color:#fffdf5;box-shadow:0 20px 44px rgba(52,61,37,.14)}
    .profile-heading-copy{position:relative;z-index:1;max-width:600px}.profile-eyebrow{margin:0 0 11px;color:#e0d2ac;font-size:9px;letter-spacing:1.8px;font-weight:800}.profile-heading h1{margin:0 0 10px;color:#fffdf5;font:normal clamp(34px,4.2vw,49px)/1.02 Georgia,serif;letter-spacing:-1px}.profile-heading h1 em{color:#e2c183;font-weight:normal}.profile-heading-copy>p:last-child{max-width:480px;margin:0;color:#e1e3d7;font-size:12px;line-height:1.7}
    .profile-heading-identity{position:relative;z-index:1;display:flex;align-items:center;gap:12px;min-width:225px;padding:14px 16px;border:1px solid rgba(255,255,255,.22);border-radius:15px;background:rgba(255,255,255,.09);backdrop-filter:blur(8px)}.heading-avatar{display:grid;place-items:center;width:52px;height:52px;flex:0 0 52px;overflow:hidden;border:2px solid rgba(255,255,255,.8);border-radius:50%;background:linear-gradient(145deg,#dfcfaa,#aeb99a);color:#4b5937;object-fit:cover;font:20px Georgia,serif}.profile-heading-identity>span:last-child{display:grid;gap:4px;min-width:0}.profile-heading-identity small{color:#e0d2ac;font-size:7px;font-weight:800;letter-spacing:1.1px}.profile-heading-identity strong{overflow:hidden;color:#fffdf5;font-size:12px;text-overflow:ellipsis;white-space:nowrap}.profile-heading-identity em{color:#e0e2d7;font-size:9px;font-style:normal}.profile-hero-orbit{position:absolute;z-index:-1;border:1px solid rgba(255,255,255,.1);border-radius:50%;pointer-events:none}.orbit-one{width:330px;height:330px;right:18%;top:-220px;box-shadow:0 0 0 17px rgba(255,255,255,.035),0 0 0 43px rgba(255,255,255,.025)}.orbit-two{width:200px;height:200px;right:23%;top:-155px}
    .artist-profile-form{display:grid;grid-template-columns:minmax(270px,.8fr) minmax(0,1.2fr);gap:17px}.profile-form-grid{display:contents}.profile-card{min-width:0;margin:0 0 17px;padding:23px;border:1px solid #e7e3d7;border-radius:18px;background:rgba(255,254,249,.92);box-shadow:0 11px 28px rgba(54,58,42,.05)}.profile-section-heading{display:flex;align-items:center;gap:11px}.profile-section-heading .section-icon{display:grid;place-items:center;width:37px;height:37px;flex:0 0 37px;border-radius:12px;background:#eff0e5;color:#63704c;font-size:16px}.profile-section-heading>div{display:grid;gap:4px}.profile-section-heading small{color:#90917e;font-size:7px;font-weight:800;letter-spacing:1.35px}.profile-section-heading h2{margin:0;color:#333d2c;font:normal 22px Georgia,serif}.profile-help{margin:10px 0 17px;color:#7b7e6e;font-size:10px;line-height:1.65}
    .photo-upload-block{margin-top:14px;padding:12px;border:1px solid #eeece2;border-radius:13px;background:linear-gradient(145deg,#fdfcf7,#f6f5ed)}.cover-preview-frame{position:relative;display:grid;place-items:center;overflow:hidden;aspect-ratio:16/8;border-radius:10px;background:radial-gradient(circle at 75% 20%,rgba(255,255,255,.7),transparent 29%),linear-gradient(145deg,#e2e5d6,#cfd5c0)}.cover-preview{display:block;width:100%;height:100%;max-height:none;margin:0;border:0;border-radius:0;object-fit:cover}.avatar-preview-frame{position:relative;display:grid;place-items:center;width:110px;height:110px;overflow:hidden;margin:0 auto 8px;border:3px solid #fff;border-radius:50%;background:linear-gradient(145deg,#e2e5d6,#cfd5c0);box-shadow:0 6px 16px rgba(65,76,44,.12)}.avatar-preview{display:block;width:100%;height:100%;margin:0;border:0;border-radius:50%;object-fit:cover}.photo-placeholder{display:grid;justify-items:center;gap:3px;color:#66714f;text-align:center}.photo-placeholder i{font-size:22px;font-style:normal;opacity:.7}.photo-placeholder strong{font:normal 15px Georgia,serif}.photo-placeholder small{margin:0!important;color:#878978!important;font-size:8px!important}.cover-preview-frame .photo-placeholder i{font-size:25px}.preview-caption{position:absolute;left:9px;bottom:9px;padding:5px 7px;border:1px solid rgba(255,255,255,.7);border-radius:20px;background:rgba(255,254,249,.8);color:#66714f;font-size:7px;font-weight:800;letter-spacing:1px;backdrop-filter:blur(5px)}.photo-upload-block>label{display:block;margin:11px 0 6px!important;color:#49543c;font-size:10px;font-weight:700}.photo-upload-block input[type=file]{display:block;width:100%;max-width:100%;padding:7px;border:1px dashed #d8dacb;border-radius:8px;background:#fffefa;color:#77796b;font:9px Arial,sans-serif}.photo-upload-block .upload-help{display:block;margin:5px 0 0;color:#8b8d7c;font-size:8px;line-height:1.5}
    .profile-fields-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px 12px}.profile-field{min-width:0}.profile-field.full-field{grid-column:1/-1}.artist-profile-form .profile-field>label,.dispatch-card .coordinate-grid label{display:block;margin:0 0 6px;color:#515a43;font-size:10px;font-weight:700}.profile-field>label b{color:#a76e55}.artist-profile-form .profile-field input:not([type=file]),.artist-profile-form .profile-field textarea,.dispatch-card input:not([type=file]){display:block;width:100%;min-height:42px;margin:0;padding:10px 11px;border:1px solid #dcdccd;border-radius:9px;background:#fffefa;color:#404738;font:11px "DM Sans",Arial,sans-serif;box-shadow:inset 0 1px 3px rgba(65,76,44,.025);transition:border-color .18s,box-shadow .18s}.artist-profile-form .profile-field textarea{min-height:105px;resize:vertical;line-height:1.6}.artist-profile-form .profile-field input:focus,.artist-profile-form .profile-field textarea:focus,.dispatch-card input:focus{outline:3px solid rgba(151,160,113,.2);border-color:#87906b}.profile-field input::placeholder,.profile-field textarea::placeholder{color:#a1a190}.profile-field>small{display:block;margin-top:5px;color:#878879;font-size:8px;line-height:1.5}
    .dispatch-card,.profile-savebar{grid-column:1/-1}.dispatch-card{padding:23px}.map-section-icon{font-size:19px!important}.dispatch-address-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(220px,.8fr);align-items:end;gap:13px}.location-help{margin:0;padding:13px 15px;border:1px solid #e6e5d9;border-left:3px solid #9ba581;border-radius:11px;background:#f7f7f0}.location-help strong{display:block;margin:0 0 4px;color:#4e5b3e;font-size:10px}.location-help p{margin:0;color:#818474;font-size:9px;line-height:1.55}.map-search-row{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0 6px}.map-action-button,.map-secondary-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:36px;padding:8px 12px;border:1px solid transparent;border-radius:8px;font:700 9px Arial,sans-serif;cursor:pointer;transition:background .18s,transform .18s}.map-action-button{background:#566541;color:#fffdf5}.map-action-button:hover{transform:translateY(-1px);background:#414c2c}.map-action-button:disabled{opacity:.65;cursor:wait}.map-action-button span{font-size:15px}.map-secondary-button{border-color:#e1dfd3;background:#fffefa;color:#626b50}.map-secondary-button:hover{background:#f0f1e8}.map-status{display:flex;align-items:center;min-height:32px;margin:8px 0;padding:7px 10px;border-radius:8px;background:#f3f3eb;color:#596744;font-size:9px;line-height:1.5}#artist-dispatch-map{position:relative;z-index:1;width:100%;height:360px;min-height:280px;overflow:hidden;border:1px solid #dcdccd;border-radius:13px;background:#eee}.map-instructions{margin:8px 0 13px;color:#828475;font-size:9px;line-height:1.55}.map-search-results{display:grid;gap:7px;margin-top:9px}.map-result-button{display:block;width:100%;padding:10px;border:1px solid #dcdccd;border-radius:8px;background:#fffefa;color:#414c2c;text-align:left;font:10px Arial,sans-serif;cursor:pointer}.map-result-button:hover{background:#f5f5ed;border-color:#aab193}.map-result-type{display:block;margin-top:3px;color:#828475;font-size:8px}.coordinate-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:10px}.dispatch-card .coordinate-grid label{margin-top:0}.dispatch-card .coordinate-help{display:block;margin-top:7px;color:#858675;font-size:8px;line-height:1.5}
    .profile-savebar{position:sticky;z-index:4;bottom:12px;display:flex;align-items:center;justify-content:space-between;gap:15px;margin:0 0 7px;padding:12px 14px 12px 17px;border:1px solid rgba(221,220,206,.85);border-radius:13px;background:rgba(255,254,249,.94);box-shadow:0 10px 30px rgba(47,53,36,.12);backdrop-filter:blur(12px)}.profile-savebar>span{color:#858675;font-size:9px}.profile-save-button{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:39px;padding:9px 14px;border:0;border-radius:9px;background:linear-gradient(110deg,#788456,#596b43);color:#fff;font:700 10px Arial,sans-serif;cursor:pointer;box-shadow:0 5px 13px rgba(85,97,58,.17);transition:transform .18s,background .18s}.profile-save-button:hover{transform:translateY(-1px);background:#414c2c}.profile-save-button span{font-size:16px}.profile-message{margin:0 0 13px;padding:12px 14px;border:1px solid transparent;border-radius:11px;font-size:11px;line-height:1.6}.profile-message.success{border-color:#d4e3c9;background:#f0f5e9;color:#47603a}.profile-message.error{border-color:#efd4cc;background:#fff1ed;color:#8b3e31}.profile-message ul{margin:5px 0 0;padding-left:18px}
    @media(max-width:850px){.artist-profile-form{grid-template-columns:1fr}.profile-form-grid{display:grid;grid-template-columns:minmax(230px,.75fr) minmax(0,1.25fr);gap:14px}.dispatch-card,.profile-savebar{grid-column:1}.profile-heading{padding:28px}.profile-heading-identity{min-width:205px}}
    @media(max-width:620px){.artist-profile-page{padding:23px 14px 55px}.profile-heading{align-items:flex-start;flex-direction:column;gap:18px;min-height:0;padding:24px 21px;border-radius:19px}.profile-heading h1{font-size:37px}.profile-heading-identity{width:100%}.profile-form-grid{grid-template-columns:1fr}.profile-card{padding:18px;border-radius:15px}.about-card{grid-row:auto}.dispatch-address-row{grid-template-columns:1fr}.coordinate-grid{grid-template-columns:1fr;gap:9px}.profile-savebar{position:static;align-items:flex-start;flex-direction:column;padding:13px}.profile-save-button{width:100%}#artist-dispatch-map{height:310px}}
    @media(prefers-reduced-motion:reduce){.map-action-button,.profile-save-button,.artist-profile-form .profile-field input:not([type=file]),.artist-profile-form .profile-field textarea{transition:none}}
</style>

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    [
        ['cover_photo', 'cover-photo-preview', 'cover-photo-placeholder'],
        ['profile_photo', 'profile-photo-preview', 'profile-photo-placeholder']
    ].forEach(function (ids) {
        const input = document.getElementById(ids[0]);
        const preview = document.getElementById(ids[1]);
        const placeholder = document.getElementById(ids[2]);
        let currentPreviewUrl = null;

        if (!input || !preview || !placeholder) {
            return;
        }

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];

            if (!file) {
                return;
            }

            if (currentPreviewUrl) {
                URL.revokeObjectURL(currentPreviewUrl);
            }

            currentPreviewUrl = URL.createObjectURL(file);
            preview.src = currentPreviewUrl;
            preview.hidden = false;
            placeholder.hidden = true;
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('artist-dispatch-map');
    const addressInput = document.getElementById('location');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const searchButton = document.getElementById('search-location-button');
    const clearButton = document.getElementById('clear-location-button');
    const statusElement = document.getElementById('map-status');
    const resultsElement = document.getElementById('map-search-results');
    const profileForm = document.getElementById('artist-profile-form');

    if (
        !mapElement ||
        !addressInput ||
        !latitudeInput ||
        !longitudeInput ||
        !searchButton ||
        !clearButton ||
        !statusElement ||
        !resultsElement ||
        !profileForm
    ) {
        return;
    }

    if (typeof L === 'undefined') {
        setStatusSafely(
            'The map library failed to load. Check your internet connection and refresh.'
        );
        return;
    }

    function setStatusSafely(message) {
        if (statusElement) {
            statusElement.textContent = message;
        }
    }

    const BD_BOUNDS = {
        minLat: 20.5,
        maxLat: 26.7,
        minLng: 88.0,
        maxLng: 92.8
    };

    const BD_CENTER = [23.685, 90.3563];

    function withinBangladesh(lat, lng) {
        return Number.isFinite(lat) &&
            Number.isFinite(lng) &&
            lat >= BD_BOUNDS.minLat &&
            lat <= BD_BOUNDS.maxLat &&
            lng >= BD_BOUNDS.minLng &&
            lng <= BD_BOUNDS.maxLng;
    }

    const map = L.map(mapElement).setView(BD_CENTER, 7);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    function setStatus(message) {
        statusElement.textContent = message;
    }

    function clearResults() {
        resultsElement.replaceChildren();
    }

    function renderAddressResults(matches) {
        clearResults();

        matches.forEach(function (match) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'map-result-button';
            button.textContent = match.label || 'Possible match in Bangladesh';
            button.addEventListener('click', function () {
                if (selectLocation(Number(match.lat), Number(match.lng), 16)) {
                    setStatus('Address match selected. Verify the marker, adjust it if needed, then save your profile.');
                    clearResults();
                }
            });
            resultsElement.appendChild(button);
        });
    }

    function updateCoordinates(lat, lng) {
        latitudeInput.value = lat.toFixed(7);
        longitudeInput.value = lng.toFixed(7);
    }

    function selectLocation(lat, lng, zoom = 17) {
        if (!withinBangladesh(lat, lng)) {
            setStatus('Please select a location inside Bangladesh.');
            return false;
        }

        if (!marker) {
            marker = L.marker([lat, lng], {
                draggable: true,
                autoPan: true
            }).addTo(map);

            marker.bindPopup('Your dispatch location');

            marker.on('dragend', function () {
                const point = marker.getLatLng();

                if (!withinBangladesh(point.lat, point.lng)) {
                    const previousLat = Number(latitudeInput.value);
                    const previousLng = Number(longitudeInput.value);

                    if (withinBangladesh(previousLat, previousLng)) {
                        marker.setLatLng([previousLat, previousLng]);
                    }

                    setStatus('The marker must remain inside Bangladesh.');
                    return;
                }

                updateCoordinates(point.lat, point.lng);

                setStatus(
                    'Location updated. Save your profile to keep the coordinates.'
                );
            });
        } else {
            marker.setLatLng([lat, lng]);
        }

        updateCoordinates(lat, lng);
        map.setView([lat, lng], zoom);

        setStatus(
            'Location selected. Check the marker and save your profile.'
        );

        return true;
    }

    /*
     * Search the existing Laravel geocoding endpoint.
     * The address field on the artist profile is named "location",
     * but the geocoding endpoint expects "shipping_address".
     */
    async function searchAddress() {
        const address = addressInput.value.trim();

        if (address.length < 10) {
            setStatus(
                'Enter a complete address, including your road or area, city, and Bangladesh.'
            );
            addressInput.focus();
            return;
        }

        if (searchButton.disabled) {
            return;
        }

        searchButton.disabled = true;
        searchButton.textContent = 'Searching...';
        clearResults();

        setStatus('Searching for your dispatch address...');

        try {
            const response = await fetch(
                @json(route('artworks.delivery.geocode')),
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },
                    body: JSON.stringify({
                        shipping_address: address
                    })
                }
            );

            let result;

            try {
                result = await response.json();
            } catch (error) {
                throw new Error(
                    'The server returned an invalid response. Refresh the page and try again.'
                );
            }

            if (!response.ok) {
                if (response.status === 401 || response.status === 403) {
                    throw new Error(
                        'You are not authorized to search addresses. Check that you are signed in and that the geocoding endpoint permits artists.'
                    );
                }

                if (response.status === 419) {
                    throw new Error(
                        'Your session or CSRF token has expired. Refresh the page and try again.'
                    );
                }

                if (response.status === 422) {
                    throw new Error(
                        result.message ||
                        'Address not found in Bangladesh. Try a more specific road, area, or landmark.'
                    );
                }

                throw new Error(
                    result.message ||
                    'Address search failed (HTTP ' + response.status + ').'
                );
            }

            const matches = Array.isArray(result.results) ? result.results : [];
            if (matches.length === 0) {
                throw new Error(
                    result.message || 'No possible matches were found. Add the area or district, or click the map to select your dispatch point.'
                );
            }

            renderAddressResults(matches);
            setStatus(matches.length === 1
                ? 'One possible match found. Select it, then position the marker precisely.'
                : matches.length + ' possible matches found. Choose the correct one, then position the marker precisely.');
        } catch (error) {
            console.error('Artist address search error:', error);

            setStatus(
                error.message ||
                'Address search failed. Check your connection or select the location directly on the map.'
            );
        } finally {
            searchButton.disabled = false;
            searchButton.textContent = 'Search address';
        }
    }

    searchButton.addEventListener('click', searchAddress);

    addressInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchAddress();
        }
    });

    /*
     * Clicking the map selects a manual dispatch location.
     */
    map.on('click', function (event) {
        if (selectLocation(event.latlng.lat, event.latlng.lng, 17)) {
            clearResults();
        }
    });

    /*
     * Keep the marker synchronized with manually edited coordinates.
     */
    function syncMapFromInputs() {
        const latText = latitudeInput.value.trim();
        const lngText = longitudeInput.value.trim();

        if (latText === '' && lngText === '') {
            return;
        }

        if (latText === '' || lngText === '') {
            setStatus('Enter both latitude and longitude.');
            return;
        }

        const lat = Number(latText);
        const lng = Number(lngText);

        if (!withinBangladesh(lat, lng)) {
            setStatus('Enter valid coordinates inside Bangladesh.');
            return;
        }

        selectLocation(lat, lng, 17);
    }

    latitudeInput.addEventListener('change', syncMapFromInputs);
    longitudeInput.addEventListener('change', syncMapFromInputs);

    /*
     * Clear the marker and both coordinate fields.
     */
    clearButton.addEventListener('click', function () {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }

        latitudeInput.value = '';
        longitudeInput.value = '';

        clearResults();
        map.setView(BD_CENTER, 7);

        setStatus(
            'Location cleared. Search for an address or click the map to select a new dispatch point.'
        );
    });

    /*
     * Coordinates must be both blank or both valid.
     */
    profileForm.addEventListener('submit', function (event) {
        const latText = latitudeInput.value.trim();
        const lngText = longitudeInput.value.trim();

        if (latText === '' && lngText === '') {
            return;
        }

        if (latText === '' || lngText === '') {
            event.preventDefault();

            setStatus(
                'Enter both coordinates, or clear both fields before saving.'
            );

            latitudeInput.focus();
            return;
        }

        const lat = Number(latText);
        const lng = Number(lngText);

        if (!withinBangladesh(lat, lng)) {
            event.preventDefault();

            setStatus(
                'Please select valid coordinates inside Bangladesh before saving.'
            );

            latitudeInput.focus();
            return;
        }

        updateCoordinates(lat, lng);
    });

    /*
     * Restore the saved dispatch location when the page loads.
     */
    const initialLatText = latitudeInput.value.trim();
    const initialLngText = longitudeInput.value.trim();

    if (initialLatText !== '' && initialLngText !== '') {
        const initialLat = Number(initialLatText);
        const initialLng = Number(initialLngText);

        if (withinBangladesh(initialLat, initialLng)) {
            selectLocation(initialLat, initialLng, 16);

            setStatus(
                'Your saved dispatch location is shown on the map.'
            );
        } else {
            setStatus(
                'Your saved coordinates are outside the supported Bangladesh map area. Select a valid location.'
            );
        }
    } else {
        setStatus(
            'Search for a street or landmark, or click the map to select your dispatch location.'
        );
    }

    /*
     * Leaflet needs to recalculate its size after the layout renders.
     */
    window.setTimeout(function () {
        map.invalidateSize();
    }, 200);
});
</script>
@endsection
