@extends('artfy.layout')

@section('title', 'Purchase artwork | Artfy')

@section('content')
<section class="detail-page">

    <a
        class="back-link"
        href="{{ route('artworks.show', 'uploaded-' . $artwork->id) }}"
    >
        ← Back to artwork
    </a>

    <div class="detail-layout">

        <div class="detail-art artwork-art green">
            <img
                class="detail-uploaded-image"
                src="{{ asset('storage/' . $artwork->image) }}"
                alt="{{ $artwork->title }}"
            >
        </div>

        <div class="detail-copy">

            <span class="eyebrow">Secure your artwork</span>

            <h1>Complete your order</h1>

            <p class="detail-description">
                You are purchasing "{{ $artwork->title }}"
                by {{ $artistName }}.
            </p>

            <div class="detail-facts">
                <span>
                    Artwork
                    <strong>{{ $artwork->title }}</strong>
                </span>

                <span>
                    Artist
                    <strong>{{ $artistName }}</strong>
                </span>

                <span>
                    Artwork price
                    <strong>
                        ৳{{ number_format((float) $artwork->price, 2) }}
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

            <p>
                Enter your delivery address, search for it, and confirm
                the exact destination on the map. Delivery is available
                within Bangladesh.
            </p>

            <div class="detail-facts">
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

            <form
                id="purchase-form"
                method="POST"
                action="{{ route('artworks.purchase.store', 'uploaded-' . $artwork->id) }}"
            >
                @csrf

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

            const lat = Number(result.lat);
            const lng = Number(result.lng);

            if (!isWithinBangladesh(lat, lng)) {
                throw new Error(
                    'The search result is outside the supported Bangladesh map area.'
                );
            }

            map.setView([lat, lng], 15);
            setDestination(
                lat,
                lng,
                'Address found. Check the marker and move it to your exact delivery point if needed.'
            );
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
