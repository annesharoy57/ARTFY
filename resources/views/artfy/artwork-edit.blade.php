@extends('artfy.layout')

@section('title', 'Edit Artwork | Artfy')

@section('content')
<section class="studio-page artwork-edit-page">

    <a class="back-link" href="{{ route('my-profile') }}">
        ← Back to My Artworks
    </a>

    <div class="artwork-edit-container">

        <p class="eyebrow">YOUR PORTFOLIO</p>

        <h1>Edit Artwork</h1>

        <p class="edit-intro">
            Update your artwork details. Changes will be submitted for
            admin review before appearing publicly again.
        </p>

        @if (session('success'))
            <div class="edit-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="edit-errors" role="alert">
                <strong>Please correct the following:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('artist.artworks.update', $artwork) }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')

            <div class="form-field">
                <label for="title">Artwork title *</label>

                <input
                    id="title"
                    name="title"
                    type="text"
                    value="{{ old('title', $artwork->title) }}"
                    maxlength="255"
                    required
                >
            </div>

            <div class="form-field">
                <label for="description">Description</label>

                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    maxlength="5000"
                >{{ old('description', $artwork->description) }}</textarea>
            </div>

            <div class="form-field">
                <label for="category">Category</label>

                <input
                    id="category"
                    name="category"
                    type="text"
                    value="{{ old('category', $artwork->category) }}"
                    maxlength="255"
                >
            </div>

            <div class="form-field">
                <label for="medium">Medium</label>

                <input
                    id="medium"
                    name="medium"
                    type="text"
                    value="{{ old('medium', $artwork->medium) }}"
                    maxlength="255"
                >
            </div>

            <div class="form-field">
                <label for="price">Price (৳)</label>

                <input
                    id="price"
                    name="price"
                    type="number"
                    step="0.01"
                    min="0"
                    max="99999999.99"
                    value="{{ old('price', $artwork->price) }}"
                >
            </div>

            <div class="form-field">
                <label>Current artwork image</label>

                @if ($artwork->image)
                    <img
                        class="current-artwork-image"
                        src="{{ asset('storage/' . ltrim($artwork->image, '/')) }}"
                        alt="{{ $artwork->title }}"
                    >
                @else
                    <p>No current image is available.</p>
                @endif

                <label for="image">Replace image (optional)</label>

                <input
                    id="image"
                    name="image"
                    type="file"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    Leave empty to keep the current image.
                    Maximum 5 MB.
                </small>
            </div>

            <div class="form-field">
                <label for="artwork_type">Artwork type *</label>

                <select id="artwork_type" name="artwork_type" required>
                    <option
                        value="physical"
                        @selected(old('artwork_type', $artwork->artwork_type) === 'physical')
                    >
                        Physical artwork
                    </option>

                    <option
                        value="digital"
                        @selected(old('artwork_type', $artwork->artwork_type) === 'digital')
                    >
                        Digital artwork
                    </option>
                </select>
            </div>

            <div class="form-field">
                <label for="digital_file">Digital artwork file</label>

                <input
                    id="digital_file"
                    name="digital_file"
                    type="file"
                    accept=".pdf,.png,.jpg,.jpeg,.webp,.zip"
                >

                <small>
                    @if ($artwork->digital_file_path)
                        A digital file is already saved. Leave this empty
                        to keep it when the artwork remains digital.
                    @else
                        A digital file is required if you select digital artwork.
                    @endif

                    Maximum 20 MB.
                </small>
            </div>

            <button class="button button-dark" type="submit">
                Save Changes →
            </button>

        </form>
    </div>
</section>

<style>
    .artwork-edit-container {
        max-width: 760px;
        width: 100%;
        margin: 30px auto;
        box-sizing: border-box;
    }

    .edit-intro {
        line-height: 1.7;
        margin-bottom: 24px;
    }

    .form-field {
        margin-bottom: 22px;
    }

    .form-field label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .form-field input:not([type="file"]),
    .form-field textarea,
    .form-field select {
        display: block;
        width: 100%;
        padding: 12px;
        border: 1px solid #d8d4cd;
        border-radius: 6px;
        font: inherit;
        box-sizing: border-box;
    }

    .form-field input[type="file"] {
        display: block;
        max-width: 100%;
        margin: 8px 0;
    }

    .form-field textarea {
        resize: vertical;
    }

    .form-field small {
        display: block;
        margin-top: 8px;
        line-height: 1.6;
        opacity: 0.75;
    }

    .current-artwork-image {
        display: block;
        max-width: 100%;
        max-height: 320px;
        object-fit: contain;
        margin: 8px 0 18px;
        border-radius: 8px;
    }

    .edit-errors {
        margin: 20px 0;
        padding: 16px;
        color: #922222;
        background: #fff0f0;
        border-radius: 8px;
    }

    .edit-errors ul {
        margin-bottom: 0;
        padding-left: 22px;
    }

    .edit-success {
        margin: 20px 0;
        padding: 14px 16px;
        color: #176534;
        background: #edf8f0;
        border-radius: 8px;
    }

    .back-link {
        display: inline-block;
        margin: 12px 0;
        text-decoration: none;
    }

    @media (max-width: 600px) {
        .artwork-edit-container {
            padding: 0 16px;
        }
    }
</style>
@endsection
