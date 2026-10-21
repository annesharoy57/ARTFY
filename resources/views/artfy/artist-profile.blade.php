@extends('artfy.layout')

@section('title', 'My Artist Profile | Artfy')

@section('content')
<div class="artist-profile-page">
    <div class="profile-heading">
        <p class="profile-eyebrow">YOUR CREATIVE SPACE</p>
        <h1>My Artist Profile</h1>
        <p>Tell the world about your art, your story, and your creative journey.</p>
    </div>

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

        <section class="profile-card">
            <h2>Profile photographs</h2>
            <p class="profile-help">
                Add a cover image and a profile picture to introduce yourself.
            </p>

            @if ($profile?->cover_photo)
                <img
                    src="{{ asset('storage/' . $profile->cover_photo) }}"
                    alt="Current cover photo"
                    class="cover-preview"
                >
            @endif

            <label for="cover_photo">Cover photo</label>
            <input
                type="file"
                id="cover_photo"
                name="cover_photo"
                accept="image/jpeg,image/png,image/webp"
            >
            <small>JPG, PNG or WebP; maximum 5 MB.</small>

            @if ($profile?->profile_photo)
                <img
                    src="{{ asset('storage/' . $profile->profile_photo) }}"
                    alt="Current profile photo"
                    class="avatar-preview"
                >
            @endif

            <label for="profile_photo">Profile photo</label>
            <input
                type="file"
                id="profile_photo"
                name="profile_photo"
                accept="image/jpeg,image/png,image/webp"
            >
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
                required
            >

            <label for="bio">Biography</label>
            <textarea
                id="bio"
                name="bio"
                rows="5"
                maxlength="2000"
                placeholder="Tell visitors about yourself and your artistic journey..."
            >{{ old('bio', $profile?->bio) }}</textarea>

            <label for="art_category">Art category</label>
            <input
                type="text"
                id="art_category"
                name="art_category"
                value="{{ old('art_category', $profile?->art_category) }}"
                maxlength="255"
                placeholder="e.g. Painting, Digital Art, Photography"
            >

            <label for="location">Location / dispatch address</label>
            <input
                type="text"
                id="location"
                name="location"
                value="{{ old('location', $profile?->location) }}"
                maxlength="255"
                placeholder="e.g. GL Road, Rangpur, Bangladesh"
                autocomplete="street-address"
            >

            <div class="location-help">
                <strong>Choose your delivery location</strong>
                <p>
                    Search for a street, road, building, or landmark.
                    Select the result on the map, then drag the marker
                    or click the map to pinpoint your actual dispatch location.
                </p>
            </div>

            <div class="map-search-row">
                <button
                    type="button"
                    id="search-location-button"
                    class="map-action-button"
                >Search address</button>

                <button
                    type="button"
                    id="clear-location-button"
                    class="map-secondary-button"
                >Clear selection</button>
            </div>

            <p id="map-status" class="map-status" role="status" aria-live="polite">
                Loading map...
            </p>

            <div
                id="artist-dispatch-map"
                role="application"
                aria-label="Select your artwork dispatch location"
            ></div>

            <div id="map-search-results" class="map-search-results"></div>

            <p class="map-instructions">
                <strong>Tip:</strong> Zoom in and place the marker at your
                actual dispatch point. A search result may identify a nearby
                road or landmark rather than your exact address.
            </p>

            <div class="coordinate-grid">
                <div>
                    <label for="latitude">Latitude</label>
                    <input
                        type="number"
                        id="latitude"
                        name="latitude"
                        value="{{ old('latitude', $profile?->latitude) }}"
                        min="-90"
                        max="90"
                        step="any"
                        placeholder="Filled from map"
                    >
                </div>

                <div>
                    <label for="longitude">Longitude</label>
                    <input
                        type="number"
                        id="longitude"
                        name="longitude"
                        value="{{ old('longitude', $profile?->longitude) }}"
                        min="-180"
                        max="180"
                        step="any"
                        placeholder="Filled from map"
                    >
                </div>
            </div>

            <small>
                Coordinates are optional while editing, but if you provide
                them, enter both. Select your actual dispatch location in
                Bangladesh for accurate delivery calculations.
            </small>

            <label for="portfolio_url">Portfolio website</label>
            <input
                type="url"
                id="portfolio_url"
                name="portfolio_url"
                value="{{ old('portfolio_url', $profile?->portfolio_url) }}"
                maxlength="255"
                placeholder="https://example.com"
            >
        </section>

        <button type="submit" class="profile-save-button">Save Profile</button>
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
</style>

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
></script>

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

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
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

            const lat = Number(result.lat);
            const lng = Number(result.lng);

            if (
                !Number.isFinite(lat) ||
                !Number.isFinite(lng) ||
                !withinBangladesh(lat, lng)
            ) {
                throw new Error(
                    'The geocoder did not return valid coordinates inside Bangladesh.'
                );
            }

            if (!selectLocation(lat, lng, 17)) {
                throw new Error(
                    'The location could not be selected. Click the map to choose it manually.'
                );
            }

            setStatus(
                'Address found. Verify the marker, then drag it or click the map to pinpoint your actual dispatch location. Save your profile when finished.'
            );
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
