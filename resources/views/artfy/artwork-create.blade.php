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

    .upload-page{position:relative;isolation:isolate;display:grid;grid-template-columns:minmax(240px,.82fr) minmax(380px,1.18fr);align-items:start;column-gap:clamp(28px,6vw,74px);max-width:1120px;min-height:680px;padding:52px 24px 72px;color:#39452f}
    .upload-page::before{position:absolute;z-index:-1;top:165px;left:5%;width:35%;height:390px;border:1px solid rgba(133,143,99,.2);border-radius:190px 190px 24px 24px;background:radial-gradient(ellipse at 50% 24%,rgba(255,255,255,.95),transparent 42%),radial-gradient(ellipse at 50% 100%,rgba(179,192,146,.45),transparent 60%),linear-gradient(155deg,#e4e8d6,#f5f1df);box-shadow:18px 20px 0 -8px rgba(177,163,119,.22),0 28px 55px rgba(65,76,44,.1);content:"";transform:rotate(-3deg)}
    .upload-page::after{position:absolute;z-index:-1;top:220px;left:11%;width:23%;height:275px;border:1px solid rgba(255,255,255,.84);border-radius:150px 150px 10px 10px;content:"ARTFY\A STUDIO";display:grid;place-items:end center;padding:25px;color:#78815f;font:700 11px/1.7 Arial,sans-serif;letter-spacing:.2em;white-space:pre;background:radial-gradient(ellipse at 50% 38%,rgba(255,255,255,.8),transparent 52%),linear-gradient(150deg,rgba(195,204,174,.75),rgba(222,211,177,.62));box-shadow:0 20px 32px rgba(65,76,44,.12);transform:rotate(4deg)}
    .upload-back{grid-column:1/-1;margin-bottom:8px;color:#647249}
    .upload-page h1{grid-column:1;grid-row:2;margin:38px 0 8px;color:#35422d;font:400 clamp(36px,4vw,52px)/1.06 Georgia,serif;letter-spacing:-.035em}
    .upload-intro{grid-column:1;grid-row:3;max-width:340px;color:#72795f;line-height:1.75}
    .upload-error{grid-column:1/-1;grid-row:4}
    .upload-form{grid-column:2;grid-row:2 / span 3;padding:32px;border:1px solid rgba(133,143,99,.24);border-radius:22px;background:rgba(255,254,249,.92);box-shadow:0 26px 64px rgba(65,76,44,.12),inset 0 1px #fff;backdrop-filter:blur(10px)}
    .upload-form label{color:#4b563f;font-size:13px}
    .upload-form input:not([type="file"]),.upload-form textarea,.upload-form select{border-color:#d9dacb;border-radius:11px;background:#fffefa;transition:border-color .2s,box-shadow .2s}
    .upload-form input:focus,.upload-form textarea:focus,.upload-form select:focus{outline:0;border-color:#89946a;box-shadow:0 0 0 4px rgba(137,148,106,.15)}
    .upload-form input[type="file"]{width:100%;padding:13px;border:1px dashed #b9bea3;border-radius:11px;background:#f7f6ed;color:#65704d}
    .upload-info{border-color:#e0e0d0;border-radius:13px;background:linear-gradient(125deg,#f7f7ee,#eeefe2);color:#4d5940}
    .upload-form button{border-radius:11px;background:linear-gradient(115deg,#788456,#596b43);box-shadow:0 8px 18px rgba(85,97,58,.18);transition:transform .2s,box-shadow .2s}
    .upload-form button:hover{transform:translateY(-2px);background:linear-gradient(115deg,#697849,#4d603b);box-shadow:0 12px 23px rgba(85,97,58,.24)}
    .upload-note{padding:14px 16px;border-radius:11px;background:#f3f2e8;color:#6d745c}
    @media(max-width:760px){.upload-page{grid-template-columns:1fr;min-height:0;padding-top:30px}.upload-page::before{top:105px;left:auto;right:6%;width:34%;height:200px;border-radius:100px}.upload-page::after{top:125px;left:auto;right:13%;width:23%;height:155px;padding:13px;font-size:9px}.upload-back,.upload-page h1,.upload-intro,.upload-error,.upload-form{grid-column:1;grid-row:auto}.upload-page h1{max-width:65%;margin:36px 0 8px}.upload-intro{max-width:62%;min-height:54px;margin-bottom:20px}.upload-form{padding:24px}}
    @media(max-width:480px){.upload-page::before{top:96px;right:4%;width:34%;height:158px}.upload-page::after{top:108px;right:10%;width:24%;height:123px}.upload-page h1{max-width:68%;font-size:35px}.upload-intro{max-width:66%;font-size:13px}.upload-form{padding:19px}}
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
