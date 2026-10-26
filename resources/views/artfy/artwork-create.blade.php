@extends('artfy.layout')

@section('title', 'Upload Artwork')

@section('content')
<div class="upload-page">
    <a class="upload-back" href="{{ route('my-profile') }}">
        ← Back to My Profile
    </a>

    <h1>Upload new artwork</h1>
    <p class="upload-intro">
        Share your creation with the Artfy community.
    </p>

    @if ($errors->any())
        <div class="upload-error">
            <strong>Please correct these issues:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        class="upload-form"
        action="{{ route('artist.artworks.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <label for="title">Artwork title *</label>
        <input
            id="title"
            name="title"
            value="{{ old('title') }}"
            maxlength="255"
            required
        >

        <label for="artwork_type">Artwork type *</label>
        <select id="artwork_type" name="artwork_type" required>
            <option value="physical"
                {{ old('artwork_type', 'physical') === 'physical' ? 'selected' : '' }}>
                Physical Artwork
            </option>
            <option value="digital"
                {{ old('artwork_type') === 'digital' ? 'selected' : '' }}>
                Digital Artwork
            </option>
        </select>

        <div id="physical-info" class="upload-info">
            <strong>Physical artwork</strong>
            <p>
                The buyer's delivery charge will be calculated using the
                delivery distance: ৳30 base charge + ৳15 per kilometre.
            </p>
            <p>
                You will need to arrange delivery after the order is paid.
            </p>
        </div>

        <div id="digital-info" class="upload-info" hidden>
            <strong>Digital artwork</strong>
            <p>
                Upload a preview image and the actual downloadable file.
                The original file will be stored privately and made available
                to the buyer only after successful payment.
            </p>
            <p>Digital artwork has no delivery charge.</p>
        </div>

        <label for="image">Artwork preview image *</label>
        <input
            id="image"
            name="image"
            type="file"
            accept=".jpg,.jpeg,.png,.webp"
            required
        >
        <small>JPG, PNG or WebP. Maximum size: 5 MB.</small>

        <div id="digital-file-group" hidden>
            <label for="digital_file">Downloadable artwork file *</label>
            <input
                id="digital_file"
                name="digital_file"
                type="file"
                accept=".pdf,.png,.jpg,.jpeg,.webp,.zip"
                disabled
            >
            <small>
                PDF, PNG, JPG, WebP or ZIP. Maximum size: 20 MB.
                This file is different from the preview image.
            </small>
        </div>

        <label for="description">Description</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            maxlength="5000"
        >{{ old('description') }}</textarea>

        <label for="category">Category</label>
        <input
            id="category"
            name="category"
            value="{{ old('category') }}"
            maxlength="255"
            placeholder="Painting, Photography, Illustration..."
        >

        <label for="medium">Medium</label>
        <input
            id="medium"
            name="medium"
            value="{{ old('medium') }}"
            maxlength="255"
            placeholder="Oil on canvas, Watercolor, Digital..."
        >

        <label for="price">Price in taka (৳)</label>
        <input
            id="price"
            name="price"
            type="number"
            min="0"
            max="99999999.99"
            step="0.01"
            value="{{ old('price') }}"
            placeholder="e.g. 20000"
        >

        <button type="submit">Submit artwork for approval</button>

        <p class="upload-note">
            Your artwork will be reviewed by an admin before it appears
            in the public gallery.
        </p>
    </form>
</div>

<style>
    .upload-page {
        max-width: 760px;
        margin: auto;
        padding: 45px 24px;
        color: #414c2c;
    }

    .upload-back {
        color: #55613a;
        text-decoration: none;
        font-weight: 600;
    }

    .upload-page h1 {
        margin: 26px 0 8px;
        font-size: clamp(30px, 5vw, 42px);
    }

    .upload-intro {
        color: #72795f;
        margin-bottom: 28px;
    }

    .upload-form {
        background: #fffef9;
        border: 1px solid #e1e2d5;
        border-radius: 16px;
        padding: 28px;
    }

    .upload-form label {
        display: block;
        margin: 18px 0 8px;
        font-weight: 600;
    }

    .upload-form input:not([type="file"]),
    .upload-form textarea,
    .upload-form select {
        width: 100%;
        box-sizing: border-box;
        padding: 13px;
        border: 1px solid #d8dacb;
        border-radius: 8px;
        background: #f5f3e8;
        color: #414c2c;
        font: inherit;
    }

    .upload-form input[type="file"] {
        display: block;
        max-width: 100%;
        font: inherit;
    }

    .upload-form small {
        display: block;
        color: #72795f;
        margin-top: 7px;
        line-height: 1.6;
    }

    .upload-info {
        margin-top: 16px;
        padding: 16px;
        border: 1px solid #d8dacb;
        border-radius: 10px;
        background: #f5f3e8;
        line-height: 1.6;
    }

    .upload-info p {
        margin: 7px 0 0;
        color: #62694f;
    }

    .upload-form button {
        margin-top: 25px;
        padding: 14px 22px;
        border: 0;
        border-radius: 8px;
        background: #55613a;
        color: white;
        font: inherit;
        font-weight: 700;
        cursor: pointer;
    }

    .upload-form button:hover {
        background: #414c2c;
    }

    .upload-note {
        margin-top: 16px;
        color: #72795f;
        font-size: 13px;
        line-height: 1.6;
    }

    .upload-error {
        background: #f8e8e5;
        color: #8a3025;
        padding: 15px 20px;
        border-radius: 9px;
        margin: 20px 0;
    }

    .upload-error ul {
        margin-bottom: 0;
    }

    [hidden] {
        display: none !important;
    }

    @media (max-width: 600px) {
        .upload-page {
            padding: 28px 16px;
        }

        .upload-form {
            padding: 20px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('artwork_type');
        const physicalInfo = document.getElementById('physical-info');
        const digitalInfo = document.getElementById('digital-info');
        const digitalGroup = document.getElementById('digital-file-group');
        const digitalFile = document.getElementById('digital_file');

        function updateArtworkType() {
            const isDigital = typeSelect.value === 'digital';

            physicalInfo.hidden = isDigital;
            digitalInfo.hidden = !isDigital;
            digitalGroup.hidden = !isDigital;

            digitalFile.disabled = !isDigital;
            digitalFile.required = isDigital;

            if (!isDigital) {
                digitalFile.value = '';
            }
        }

        typeSelect.addEventListener('change', updateArtworkType);
        updateArtworkType();
    });
</script>
@endsection
