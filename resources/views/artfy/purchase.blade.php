@extends('artfy.layout')

@section('title', 'Purchase artwork | Artfy')

@section('content')
@php($isDigitalArtwork = $isDigitalArtwork ?? false)
<section class="detail-page purchase-page">

    <a
        class="back-link"
        href="{{ $isExhibitionArtwork
            ? route('exhibitions.show', $exhibition)
            : route('artworks.show', 'uploaded-' . $artwork->id) }}"
    >
        {{ $isExhibitionArtwork ? '← Back to exhibition' : '← Back to artwork' }}
    </a>

    <div class="detail-layout purchase-layout">

        <div class="detail-art artwork-art green purchase-art-panel">
            <img
                class="detail-uploaded-image"
                src="{{ asset('storage/' . $artwork->image) }}"
                alt="{{ $artwork->title }}"
            >
            <span class="purchase-image-caption">ARTFY · ORIGINAL ARTWORK</span>
        </div>

        <div class="detail-copy">

            <span class="eyebrow">{{ $isExhibitionArtwork ? 'Exhibition collection' : 'Secure your artwork' }}</span>

            <h1>Complete your order</h1>

            <p class="detail-description">
                You are purchasing "{{ $artwork->title }}"
                by {{ $artistName }}.
            </p>

            <div class="detail-facts purchase-facts">
                <span>
                    Artwork
                    <strong>{{ $artwork->title }}</strong>
                </span>

                <span>
                    Artist
                    <strong>{{ $artistName }}</strong>
                </span>

                <span>
                    {{ $isDigitalArtwork ? 'Digital format' : 'Artwork price' }}
                    <strong>
                        {{ $isDigitalArtwork ? 'PDF download' : 'BDT ' . number_format((float) $artwork->price, 2) }}
                    </strong>
                </span>
            </div>

            @if (session('error'))
                <div role="alert" class="checkout-message checkout-error">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div role="status" class="checkout-message checkout-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="checkout-message checkout-error">
                    <strong>Please correct the following:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                id="purchase-form"
                method="POST"
                action="{{ route('artworks.purchase.store', $purchaseSlug) }}"
            >
                @csrf

            @if ($isDigitalArtwork)
                <div class="digital-delivery-note">
                    <strong>Instant digital delivery</strong>
                    <span>This artwork is supplied as a PDF. After SSLCOMMERZ confirms your payment, download it securely from My Purchases.</span>
                </div>
            @else
            <p>
                Enter your delivery address, search for it, and confirm
                the exact destination on the map. Delivery is available
                within Bangladesh.
            </p>

            <div class="detail-facts purchase-facts purchase-delivery-facts">
                <span>
                    Delivery fee
                    <strong>Calculated from road distance</strong>
                </span>

                <span>
                    Total to pay
                    <strong>Artwork price + delivery fee</strong>
                </span>
            </div>

            <p>
                <strong>Delivery fee:</strong> Minimum ৳40 and maximum ৳300.
                Your final delivery fee and total are calculated securely
                on the server.
            </p>

                <div class="form-group checkout-field">
                    <label for="shipping_address">
                        Delivery address
                    </label>

                    <textarea
                        id="shipping_address"
                        name="shipping_address"
                        rows="4"
                        required
                        minlength="10"
                        maxlength="1000"
                        placeholder="House/building, road, area, city and postcode"
                        aria-describedby="shipping-address-help"
                    >{{ old('shipping_address') }}</textarea>

                    @error('shipping_address')
                        <p class="error-message" role="alert">
                            {{ $message }}
                        </p>
                    @enderror

                    <p id="shipping-address-help" class="checkout-help">
                        Enter your complete address, then click
                        <strong>Find address on map</strong>.
                    </p>
                </div>

                <div class="checkout-map-panel">
                    <h2>Confirm your delivery location</h2>

                    <p class="checkout-help">
                        Search for your address, select the result, then
                        move the marker if necessary. You can also click
                        directly on the map.
                    </p>

                    <button
                        type="button"
                        id="find-address-button"
                        class="button button-dark map-search-button"
                    >
                        Find address on map
                    </button>

                    <div id="address-search-results" class="address-search-results" hidden aria-label="Possible address matches"></div>

                    <p
                        id="map-status"
                        class="checkout-map-status"
                        role="status"
                        aria-live="polite"
                    >
                        Search for your address or click the map to select
                        your destination.
                    </p>

                    <div
                        id="delivery-map"
                        aria-label="Interactive delivery location map"
                    ></div>

                    <div class="selected-coordinates">
                        <span>
                            Latitude:
                            <strong id="selected-latitude">Not selected</strong>
                        </span>

                        <span>
                            Longitude:
                            <strong id="selected-longitude">Not selected</strong>
                        </span>
                    </div>

                    <p class="checkout-help">
                        Confirm that the marker points to your actual
                        delivery address before paying.
                    </p>
                </div>

                <input
                    type="hidden"
                    id="shipping_latitude"
                    name="shipping_latitude"
                    value="{{ old('shipping_latitude') }}"
                    required
                >

                <input
                    type="hidden"
                    id="shipping_longitude"
                    name="shipping_longitude"
                    value="{{ old('shipping_longitude') }}"
                    required
                >

                @error('shipping_latitude')
                    <p class="error-message" role="alert">
                        {{ $message }}
                    </p>
                @enderror

                @error('shipping_longitude')
                    <p class="error-message" role="alert">
                        {{ $message }}
                    </p>
                @enderror
            @endif

                <p
                    id="location-validation-message"
                    class="error-message"
                    role="alert"
                    hidden
                >
                    Please search for your delivery address or select your
                    location on the map before continuing.
                </p>

                <button
                    id="pay-button"
                    class="button button-dark"
                    type="submit"
                >
                    Pay securely with SSLCOMMERZ →
                </button>
            </form>

            <p class="checkout-help">
                You will be redirected to SSLCOMMERZ to complete payment
                using the available payment options.
            </p>

            <p>
                <strong>Payment security:</strong>
                Your order will be confirmed only after Artfy verifies
                the payment with SSLCOMMERZ. If a payment appears
                successful but your order is not confirmed, contact
                Artfy support before attempting another payment.
            </p>

        </div>
    </div>
</section>
@endsection

@push('styles')
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
>

<style>
    .purchase-page {
        --checkout-olive: #536342;
        --checkout-deep: #33422f;
        --checkout-muted: #747967;
        --checkout-line: #e4e2d5;
        max-width: 1440px;
        margin: 0 auto;
        padding: 34px clamp(20px, 5vw, 76px) 86px;
        background: radial-gradient(ellipse at 14% 8%, rgba(221,225,202,.38), transparent 32%), #faf9f4;
    }
    .purchase-page .back-link {
        display: inline-flex; align-items: center; min-height: 42px; margin: 0 0 23px;
        padding: 0 15px; border: 1px solid var(--checkout-line); border-radius: 999px;
        background: rgba(255,255,255,.72); color: var(--checkout-olive); font-size: 13px;
        font-weight: 700; text-decoration: none; transition: transform .2s, box-shadow .2s;
    }
    .purchase-page .back-link:hover { transform: translateX(-3px); box-shadow: 0 8px 18px rgba(51,66,47,.09); }
    .purchase-layout { max-width: 1180px; align-items: start; grid-template-columns: minmax(0,.92fr) minmax(420px,1.08fr); gap: clamp(28px,5vw,70px); }
    .purchase-art-panel {
        position: sticky; top: 28px; min-height: 0; aspect-ratio: 4 / 5; padding: clamp(16px,2vw,28px);
        place-items: center; border: 1px solid rgba(132,141,105,.28); border-radius: 24px;
        background: radial-gradient(ellipse at 50% 35%,#fffef9 0,#f0efe3 58%,#e3e7d7 100%);
        box-shadow: 0 28px 65px rgba(60,72,47,.13), inset 0 0 0 8px rgba(255,255,255,.34);
    }
    .purchase-art-panel::before { inset: 20px; border: 1px solid rgba(83,99,66,.13); border-radius: 15px; }
    .purchase-art-panel::after { display: none; }
    .purchase-art-panel .detail-uploaded-image {
        position: relative; z-index: 1; display: block; width: 100%; height: 100%; max-height: 700px;
        object-fit: contain; border: 10px solid #fffef9; border-radius: 4px;
        box-shadow: 0 24px 40px rgba(47,55,38,.22);
        transform: perspective(1200px) rotateY(-3deg) rotateX(1deg); transition: transform .45s, box-shadow .45s;
    }
    .purchase-art-panel:hover .detail-uploaded-image { transform: perspective(1200px) rotateY(0) rotateX(0) translateY(-4px); box-shadow: 0 30px 48px rgba(47,55,38,.24); }
    .purchase-image-caption {
        position: absolute; z-index: 2; bottom: 24px; left: 50%; padding: 8px 13px;
        border: 1px solid rgba(255,255,255,.55); border-radius: 999px; background: rgba(255,254,249,.88);
        color: var(--checkout-olive); font-size: 9px; font-weight: 800; letter-spacing: .16em;
        white-space: nowrap; transform: translateX(-50%); backdrop-filter: blur(8px);
    }
    .purchase-page .detail-copy { padding: clamp(24px,3vw,40px); border: 1px solid var(--checkout-line); border-radius: 24px; background: rgba(255,254,249,.9); box-shadow: 0 18px 48px rgba(60,72,47,.08); }
    .purchase-page .detail-copy .eyebrow { color: #8a906d; font-size: 10px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
    .purchase-page .detail-copy h1 { color: var(--checkout-deep); font-size: clamp(36px,4vw,52px); line-height: 1.05; }
    .purchase-page .detail-description { font-size: 15px; }
    .purchase-page .purchase-facts { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 16px; margin: 22px 0; padding: 19px; border: 1px solid var(--checkout-line); border-radius: 16px; background: linear-gradient(135deg,#fffef9,#f4f3e9); }
    .purchase-page .purchase-facts span { color: var(--checkout-muted); font-size: 11px; }
    .purchase-page .purchase-facts strong { color: var(--checkout-deep); font-size: 13px; line-height: 1.45; }
    .purchase-page .purchase-delivery-facts { grid-template-columns: 1fr 1fr; }
    .purchase-page .checkout-field label { color: var(--checkout-deep); font-weight: 700; }
    .purchase-page #shipping_address { min-height: 116px; padding: 14px 16px; border-color: #d9dacb; border-radius: 13px; background: #fff; transition: border-color .2s, box-shadow .2s; }
    .purchase-page #shipping_address:focus { border-color: #83906b; outline: none; box-shadow: 0 0 0 4px rgba(131,144,107,.15); }
    .purchase-page .checkout-help { color: var(--checkout-muted); font-size: 12px; }
    .purchase-page .checkout-map-panel { margin: 25px 0 18px; padding: 20px; border-color: var(--checkout-line); border-radius: 18px; background: #f8f7f0; }
    .purchase-page .checkout-map-panel h2 { color: var(--checkout-deep); font: 500 24px Georgia,serif; }
    .purchase-page #delivery-map { height: 340px; border: 1px solid #d9dacb; border-radius: 13px; box-shadow: 0 8px 20px rgba(51,66,47,.08); }
    .purchase-page .map-search-button { min-height: 42px; border-radius: 11px; }
    .purchase-page .address-search-results { display: grid; gap: 7px; margin: 12px 0; }
    .purchase-page .address-search-results[hidden] { display: none !important; }
    .purchase-page .address-result-option { width: 100%; padding: 11px 13px; border: 1px solid #dfe1d3; border-radius: 11px; background: #fffef9; color: #536342; text-align: left; font: inherit; font-size: 12px; line-height: 1.5; cursor: pointer; }
    .purchase-page .address-result-option:hover,.purchase-page .address-result-option:focus-visible { border-color: #899675; background: #f0f3e8; outline: none; }
    .purchase-page .checkout-map-status,.purchase-page .selected-coordinates { color: var(--checkout-muted); font-size: 12px; }
    .purchase-page #pay-button { min-height: 54px; border-radius: 13px; background: linear-gradient(115deg,#65784e,#465b3b); box-shadow: 0 12px 24px rgba(70,91,59,.2); font-weight: 800; }
    .purchase-page #pay-button:hover { transform: translateY(-2px); box-shadow: 0 16px 28px rgba(70,91,59,.25); }
    .purchase-page .checkout-message { padding: 14px 16px; border: 1px solid var(--checkout-line); border-radius: 13px; line-height: 1.6; }
    .purchase-page .checkout-error { border-color: #edc7bd; background: #fff5f1; }
    .purchase-page .checkout-success { border-color: #c7ddc9; background: #f1f8f0; }
    .purchase-page .digital-delivery-note { display: grid; gap: 7px; margin: 25px 0; padding: 18px 20px; border: 1px solid #d9dfc9; border-radius: 16px; background: linear-gradient(135deg,#f4f6ec,#fffef8); color: #536342; line-height: 1.6; }
    .purchase-page .digital-delivery-note strong { color: #33422f; font: 500 21px Georgia,serif; }
    .purchase-page .digital-delivery-note span { font-size: 13px; }

    .checkout-field {
        margin: 20px 0;
    }

    #shipping_address {
        width: 100%;
        box-sizing: border-box;
        display: block;
        padding: 12px;
        border: 1px solid #cfcfcf;
        border-radius: 8px;
        font: inherit;
    }

    .checkout-help {
        font-size: 0.9rem;
        line-height: 1.5;
        margin-top: 8px;
    }

    .checkout-message {
        margin: 16px 0;
        padding: 12px;
        border-radius: 8px;
    }

    .checkout-error,
    .error-message {
        color: #a52a2a;
    }

    .checkout-success {
        color: #386641;
    }

    .checkout-map-panel {
        margin: 24px 0;
        padding: 16px;
        border: 1px solid #dedede;
        border-radius: 12px;
    }

    .checkout-map-panel h2 {
        margin-top: 0;
        font-size: 1.25rem;
    }

    .map-search-button {
        margin: 8px 0;
    }

    #delivery-map {
        width: 100%;
        height: 380px;
        min-height: 300px;
        border-radius: 10px;
        overflow: hidden;
        background: #eeeeee;
        z-index: 1;
    }

    .checkout-map-status {
        font-size: 0.9rem;
        line-height: 1.5;
        margin: 10px 0;
    }

    .selected-coordinates {
        display: flex;
        flex-wrap: wrap;
        gap: 8px 20px;
        margin-top: 12px;
        font-size: 0.9rem;
        overflow-wrap: anywhere;
    }

    #pay-button {
        width: 100%;
        margin-top: 12px;
    }

    @media (max-width: 600px) {
        .purchase-layout { grid-template-columns: minmax(0,1fr); }
        .purchase-art-panel { position: relative; top: auto; aspect-ratio: 1 / 1; }
        .purchase-page .detail-copy { padding: 22px 18px; }
        .purchase-page .purchase-facts { padding: 15px; }
        .purchase-page #delivery-map { height: 290px; }
        #delivery-map {
            height: 300px;
        }

        .checkout-map-panel {
            padding: 12px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('delivery-map');

    if (!mapElement || typeof L === 'undefined') {
        return;
    }

    const addressInput = document.getElementById('shipping_address');
    const latitudeInput = document.getElementById('shipping_latitude');
    const longitudeInput = document.getElementById('shipping_longitude');
    const findButton = document.getElementById('find-address-button');
    const statusElement = document.getElementById('map-status');
    const resultsElement = document.getElementById('address-search-results');
    const latitudeDisplay = document.getElementById('selected-latitude');
    const longitudeDisplay = document.getElementById('selected-longitude');
    const form = document.getElementById('purchase-form');
    const validationMessage = document.getElementById('location-validation-message');

    // Initial view of Bangladesh. A destination is not selected yet.
    const map = L.map('delivery-map').setView([23.685, 90.3563], 7);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'
    }).addTo(map);

    let marker = null;
    let selectedLocation = false;
    let searchInProgress = false;

    // Reuse coordinates retained after a validation error, if present.
    const oldLat = Number(latitudeInput.value);
    const oldLng = Number(longitudeInput.value);

    function renderAddressResults(results) {
        resultsElement.replaceChildren();
        resultsElement.hidden = results.length === 0;

        results.forEach(function (result, index) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'address-result-option';
            button.textContent = (index + 1) + '. ' + (result.label || 'Possible match');
            button.addEventListener('click', function () {
                const lat = Number(result.lat);
                const lng = Number(result.lng);
                map.setView([lat, lng], 16);
                setDestination(
                    lat,
                    lng,
                    'Approximate place selected. Drag the marker or click the map to pinpoint the exact delivery address.'
                );
            });
            resultsElement.appendChild(button);
        });
    }

    function isWithinBangladesh(lat, lng) {
        // Approximate bounding box only; not an exact national boundary.
        return Number.isFinite(lat)
            && Number.isFinite(lng)
            && lat >= 20.5
            && lat <= 26.7
            && lng >= 88.0
            && lng <= 92.8;
    }

    function clearSelection(message) {
        selectedLocation = false;
        latitudeInput.value = '';
        longitudeInput.value = '';
        latitudeDisplay.textContent = 'Not selected';
        longitudeDisplay.textContent = 'Not selected';
        validationMessage.hidden = true;

        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }

        if (message) {
            statusElement.textContent = message;
        }
    }

    function setDestination(lat, lng, message) {
        if (!isWithinBangladesh(lat, lng)) {
            clearSelection(
                'That point is outside the supported Bangladesh map area. Select a location within Bangladesh.'
            );
            return false;
        }

        selectedLocation = true;
        latitudeInput.value = lat.toFixed(7);
        longitudeInput.value = lng.toFixed(7);

        latitudeDisplay.textContent = lat.toFixed(7);
        longitudeDisplay.textContent = lng.toFixed(7);

        if (!marker) {
            marker = L.marker([lat, lng], {
                draggable: true,
                autoPan: true
            }).addTo(map);

            marker.on('dragend', function (event) {
                const position = event.target.getLatLng();

                setDestination(
                    position.lat,
                    position.lng,
                    'Marker moved. Your delivery destination has been updated.'
                );
            });
        } else {
            marker.setLatLng([lat, lng]);
        }

        statusElement.textContent = message
            || 'Delivery location selected. Check the marker before paying.';

        validationMessage.hidden = true;
        return true;
    }

    map.on('click', function (event) {
        setDestination(
            event.latlng.lat,
            event.latlng.lng,
            'Map location selected. Drag the marker to adjust it if necessary.'
        );
    });

    async function findAddress() {
        const address = addressInput.value.trim();

        if (address.length < 10) {
            statusElement.textContent =
                'Enter a more complete delivery address before searching.';
            addressInput.focus();
            return;
        }

        if (searchInProgress) {
            return;
        }

        searchInProgress = true;
        resultsElement.replaceChildren();
        resultsElement.hidden = true;
        findButton.disabled = true;
        findButton.textContent = 'Searching...';
        statusElement.textContent = 'Searching for your address...';

        try {
            // This endpoint must be added to routes/web.php and the controller.
            const response = await fetch(
                @json(route('artworks.delivery.geocode')),
                {
                    method: 'POST',
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

            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message
                    || 'We could not find this address. Try adding your area, city and postcode.'
                );
            }

            const results = Array.isArray(result.results) ? result.results : [];
            if (results.length === 0) {
                throw new Error('No possible matches were found. Add the area and district, or click your delivery point directly on the map.');
            }

            renderAddressResults(results);
            statusElement.textContent = results.length === 1
                ? 'One possible place found. Select it, then position the marker precisely.'
                : results.length + ' possible places found. Choose the correct one, then position the marker precisely.';
        } catch (error) {
            clearSelection(
                error.message
                || 'Could not search for this address. Please try again.'
            );
        } finally {
            searchInProgress = false;
            findButton.disabled = false;
            findButton.textContent = 'Find address on map';
        }
    }

    findButton.addEventListener('click', findAddress);

    // Changing the address invalidates the previous marker confirmation.
    addressInput.addEventListener('input', function () {
        resultsElement.replaceChildren();
        resultsElement.hidden = true;
        if (selectedLocation) {
            clearSelection(
                'The address changed. Search again or select the matching location on the map.'
            );
        }
    });

    form.addEventListener('submit', function (event) {
        const address = addressInput.value.trim();
        const lat = Number(latitudeInput.value);
        const lng = Number(longitudeInput.value);

        if (
            address.length < 10
            || !selectedLocation
            || !isWithinBangladesh(lat, lng)
        ) {
            event.preventDefault();
            validationMessage.hidden = false;

            statusElement.textContent =
                'Please search for your address or select a valid destination on the map before paying.';

            validationMessage.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    });

    if (
        latitudeInput.value !== ''
        && longitudeInput.value !== ''
        && isWithinBangladesh(oldLat, oldLng)
    ) {
        map.setView([oldLat, oldLng], 15);
        setDestination(
            oldLat,
            oldLng,
            'Previously selected location restored. Please verify it before paying.'
        );
    }

    // Leaflet may initialize before a surrounding layout finishes sizing.
    setTimeout(function () {
        map.invalidateSize();
    }, 200);
});
</script>
@endpush
