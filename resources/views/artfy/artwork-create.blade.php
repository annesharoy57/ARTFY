
@extends('artfy.layout')

@section('title', 'Upload Artwork')

@section('content')
<div class="upload-page">
    <a class="upload-back" href="{{ route('artist.studio') }}">← Back to Artist Studio</a>

    <h1>Upload new artwork</h1>
    <p class="upload-intro">Share your creation with the Artfy community.</p>

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
        <input id="title" name="title" value="{{ old('title') }}" maxlength="255" required>

        <label for="image">Artwork image *</label>
        <input
            id="image"
            name="image"
            type="file"
            accept=".jpg,.jpeg,.png,.webp"
            required
        >
        <small>JPG, PNG or WebP. Maximum size: 5 MB.</small>

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
            placeholder="Painting, Digital Art, Photography..."
        >

        <label for="medium">Medium</label>
        <input
            id="medium"
            name="medium"
            value="{{ old('medium') }}"
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

        <button type="submit">Publish artwork</button>
    </form>
</div>

<style>
    .upload-page {
        max-width: 760px;
        margin: auto;
        padding: 45px 24px;
        color: #414c2c;
    }
    .upload-back { color: #55613a; text-decoration: none; font-weight: 600; }
    .upload-page h1 { margin: 26px 0 8px; font-size: clamp(30px, 5vw, 42px); }
    .upload-intro { color: #72795f; margin-bottom: 28px; }
    .upload-form {
        background: #fffef9;
        border: 1px solid #e1e2d5;
        border-radius: 16px;
        padding: 28px;
    }
    .upload-form label { display: block; margin: 18px 0 8px; font-weight: 600; }
    .upload-form input:not([type="file"]), .upload-form textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 13px;
        border: 1px solid #d8dacb;
        border-radius: 8px;
        background: #f5f3e8;
        color: #414c2c;
        font: inherit;
    }
    .upload-form input[type="file"] { display: block; max-width: 100%; }
    .upload-form small { display: block; color: #72795f; margin-top: 7px; }
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
    .upload-form button:hover { background: #414c2c; }
    .upload-error {
        background: #f8e8e5;
        color: #8a3025;
        padding: 15px 20px;
        border-radius: 9px;
        margin: 20px 0;
    }
    @media (max-width: 600px) {
        .upload-page { padding: 28px 16px; }
        .upload-form { padding: 20px; }
    }
</style>
@endsection
