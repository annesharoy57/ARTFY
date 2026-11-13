@extends('artfy.layout')

@section('title', 'Edit Profile | Artfy')

@section('content')
<main class="lover-editor">
    <a class="lover-editor-back" href="{{ route('my-profile') }}">
        <span aria-hidden="true">&larr;</span> Back to My Profile
    </a>

    <header class="lover-editor-heading">
        <span class="lover-editor-eyebrow">YOUR ARTFY ACCOUNT</span>
        <h1>Make it <em>yours.</em></h1>
        <p>Keep your details up to date and let the community know a little more about you.</p>
    </header>

    @if ($errors->any())
        <div class="lover-editor-errors" role="alert">
            <strong>There are a few things to check:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="lover-editor-layout">
        <form class="lover-editor-form" action="{{ route('my-profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <section class="lover-editor-section">
                <div class="lover-editor-section-heading">
                    <span class="lover-editor-step">01</span>
                    <div>
                        <h2>Personal details</h2>
                        <p>The basics that help us identify your account.</p>
                    </div>
                </div>

                <div class="lover-editor-fields">
                    <div class="lover-editor-field">
                        <label for="name">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                        @error('name')<span class="lover-editor-field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="lover-editor-field">
                        <label for="email">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                        @error('email')<span class="lover-editor-field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="lover-editor-field">
                        <label for="phone">Phone number <span>Optional</span></label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="Add a contact number">
                        @error('phone')<span class="lover-editor-field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="lover-editor-field lover-editor-field-wide">
                        <label for="address">Address <span>Optional</span></label>
                        <textarea id="address" name="address" rows="3" autocomplete="street-address" placeholder="City, region, or country">{{ old('address', $user->address) }}</textarea>
                        @error('address')<span class="lover-editor-field-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </section>

            <section class="lover-editor-section lover-photo-section">
                <div class="lover-editor-section-heading">
                    <span class="lover-editor-step">02</span>
                    <div>
                        <h2>Profile photo</h2>
                        <p>Choose an image that feels like you.</p>
                    </div>
                </div>

                <label class="lover-photo-upload" for="profile_photo">
                    <span class="lover-upload-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5"/><path d="M5 14v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5"/></svg>
                    </span>
                    <span><strong>Choose a new photo</strong><small>JPG, JPEG, PNG, or WEBP · maximum 2 MB</small></span>
                    <input id="profile_photo" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                </label>
                @error('profile_photo')<span class="lover-editor-field-error">{{ $message }}</span>@enderror
            </section>

            <div class="lover-editor-actions">
                <a href="{{ route('my-profile') }}" class="lover-editor-cancel">Cancel</a>
                <button type="submit" class="lover-editor-save">
                    Save profile <span aria-hidden="true">&rarr;</span>
                </button>
            </div>
        </form>

        <aside class="lover-editor-preview" aria-label="Profile preview">
            <div class="lover-preview-art" aria-hidden="true"><span>ARTFY</span></div>
            <div class="lover-preview-body">
                <span class="lover-preview-label">PROFILE PREVIEW</span>
                <div class="lover-preview-avatar">
                    @if ($user->profile_photo)
                        <img src="{{ asset('storage/' . ltrim($user->profile_photo, '/')) }}" alt="">
                    @else
                        {{ strtoupper(substr($user->name ?: 'A', 0, 1)) }}
                    @endif
                </div>
                <h2>{{ old('name', $user->name) }}</h2>
                <span class="lover-preview-role">Art Lover</span>
                <p>Your profile is your own little corner of the Artfy community.</p>
                <div class="lover-preview-rule"><span></span><i>✦</i><span></span></div>
                <small>Discover · Appreciate · Collect</small>
            </div>
        </aside>
    </div>
</main>

<style>
    .lover-editor {
        --editor-olive: #4c5a36;
        --editor-deep: #35432b;
        --editor-muted: #7a806c;
        --editor-border: #e4e2d6;
        max-width: 1180px;
        margin: 0 auto;
        padding: 40px 24px 76px;
        color: var(--editor-deep);
    }

    .lover-editor-back { display: inline-flex; align-items: center; gap: 9px; color: var(--editor-olive); font-size: 13px; font-weight: 700; text-decoration: none; }
    .lover-editor-back:hover { text-decoration: underline; text-underline-offset: 4px; }
    .lover-editor-back span { font-size: 18px; transition: transform .18s ease; }
    .lover-editor-back:hover span { transform: translateX(-3px); }

    .lover-editor-heading { max-width: 660px; margin: 28px 0 30px; }
    .lover-editor-eyebrow { color: #92916f; font-size: 10px; font-weight: 800; letter-spacing: .2em; }
    .lover-editor-heading h1 { margin: 10px 0 8px; color: var(--editor-deep); font-family: 'Playfair Display', Georgia, serif; font-size: clamp(36px, 5vw, 58px); font-weight: 500; letter-spacing: -.035em; line-height: 1.08; }
    .lover-editor-heading h1 em { color: #b59158; font-weight: 400; }
    .lover-editor-heading p { max-width: 510px; margin: 0; color: var(--editor-muted); font-size: 14px; line-height: 1.8; }

    .lover-editor-errors { margin: 0 0 22px; padding: 15px 18px; border: 1px solid #edcfc8; border-radius: 12px; background: #fff3ef; color: #8b3020; font-size: 13px; }
    .lover-editor-errors ul { margin: 8px 0 0; padding-left: 20px; line-height: 1.65; }

    .lover-editor-layout { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(260px, .85fr); align-items: start; gap: 22px; }
    .lover-editor-form { min-width: 0; padding: 30px; border: 1px solid var(--editor-border); border-radius: 22px; background: #fffefa; box-shadow: 0 16px 42px rgba(55, 64, 42, .07); }
    .lover-editor-section + .lover-editor-section { margin-top: 32px; padding-top: 27px; border-top: 1px solid #eeece2; }
    .lover-editor-section-heading { display: flex; align-items: flex-start; gap: 13px; margin-bottom: 22px; }
    .lover-editor-step { display: grid; width: 35px; height: 35px; flex: 0 0 35px; place-items: center; border: 1px solid #e1e3d5; border-radius: 11px; background: #f1f2e9; color: var(--editor-olive); font-family: Georgia, serif; font-size: 13px; }
    .lover-editor-section-heading h2 { margin: 1px 0 4px; color: var(--editor-deep); font-family: 'Playfair Display', Georgia, serif; font-size: 21px; font-weight: 500; }
    .lover-editor-section-heading p { margin: 0; color: var(--editor-muted); font-size: 12px; line-height: 1.6; }

    .lover-editor-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 16px; }
    .lover-editor-field { min-width: 0; }
    .lover-editor-field-wide { grid-column: 1 / -1; }
    .lover-editor-field label { display: flex; align-items: center; gap: 8px; margin-bottom: 7px; color: #414836; font-size: 12px; font-weight: 700; }
    .lover-editor-field label span { color: #989b8b; font-size: 10px; font-weight: 500; }
    .lover-editor-field input, .lover-editor-field textarea { display: block; width: 100%; padding: 12px 13px; border: 1px solid #dedfd2; border-radius: 11px; outline: none; background: #fff; color: #32382c; font: inherit; font-size: 13px; line-height: 1.5; transition: border-color .18s ease, box-shadow .18s ease, background .18s ease; }
    .lover-editor-field textarea { min-height: 94px; resize: vertical; }
    .lover-editor-field input::placeholder, .lover-editor-field textarea::placeholder { color: #adb0a2; }
    .lover-editor-field input:focus, .lover-editor-field textarea:focus { border-color: #84916a; background: #fffefa; box-shadow: 0 0 0 4px rgba(85, 97, 58, .11); }
    .lover-editor-field-error { display: block; margin-top: 6px; color: #a33128; font-size: 11px; }

    .lover-photo-upload { display: flex; align-items: center; gap: 14px; min-height: 82px; padding: 15px; border: 1px dashed #cfd4c0; border-radius: 14px; background: #f8f8f2; cursor: pointer; transition: border-color .2s ease, background .2s ease; }
    .lover-photo-upload:hover { border-color: #87936c; background: #f2f3e9; }
    .lover-upload-icon { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; border-radius: 13px; background: #e8ebdd; color: var(--editor-olive); }
    .lover-upload-icon svg { width: 21px; height: 21px; fill: none; stroke: currentColor; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; }
    .lover-photo-upload strong, .lover-photo-upload small { display: block; }
    .lover-photo-upload strong { color: #414836; font-size: 12px; }
    .lover-photo-upload small { margin-top: 4px; color: var(--editor-muted); font-size: 10px; line-height: 1.5; }
    .lover-photo-upload input { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; clip-path: inset(50%); }
    .lover-photo-upload:focus-within { outline: 3px solid rgba(85, 97, 58, .2); outline-offset: 3px; }

    .lover-editor-actions { display: flex; align-items: center; justify-content: flex-end; gap: 18px; margin-top: 30px; padding-top: 22px; border-top: 1px solid #eeece2; }
    .lover-editor-cancel { color: var(--editor-muted); font-size: 12px; font-weight: 700; text-decoration: none; }
    .lover-editor-cancel:hover { color: var(--editor-deep); text-decoration: underline; }
    .lover-editor-save { display: inline-flex; align-items: center; justify-content: center; gap: 12px; min-height: 45px; padding: 11px 18px; border: 1px solid var(--editor-olive); border-radius: 999px; background: var(--editor-olive); color: #fff; font: inherit; font-size: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 7px 16px rgba(65, 76, 44, .16); transition: background .18s ease, transform .18s ease, box-shadow .18s ease; }
    .lover-editor-save:hover { transform: translateY(-2px); background: var(--editor-deep); box-shadow: 0 11px 22px rgba(65, 76, 44, .2); }
    .lover-editor-save:focus-visible, .lover-editor-cancel:focus-visible, .lover-editor-back:focus-visible { outline: 3px solid rgba(85, 97, 58, .25); outline-offset: 3px; }

    .lover-editor-preview { position: sticky; top: 24px; overflow: hidden; border: 1px solid #dfe1d2; border-radius: 22px; background: #fffefa; box-shadow: 0 16px 42px rgba(55, 64, 42, .07); }
    .lover-preview-art { position: relative; display: flex; min-height: 125px; align-items: flex-end; overflow: hidden; padding: 15px 18px; background: radial-gradient(circle at 75% 20%, rgba(210, 190, 139, .45), transparent 25%), linear-gradient(135deg, #dfe3d2, #aeb997 58%, #e8e4d4); }
    .lover-preview-art::before, .lover-preview-art::after { position: absolute; border: 1px solid rgba(255,255,255,.42); border-radius: 50%; content: ''; }
    .lover-preview-art::before { width: 190px; height: 190px; right: -30px; top: -105px; }
    .lover-preview-art::after { width: 130px; height: 130px; right: 52px; top: -72px; }
    .lover-preview-art span { position: relative; color: rgba(255,255,255,.85); font-size: 9px; font-weight: 800; letter-spacing: .24em; }
    .lover-preview-body { padding: 23px 24px 25px; text-align: center; }
    .lover-preview-label { color: #9a987c; font-size: 9px; font-weight: 800; letter-spacing: .17em; }
    .lover-preview-avatar { display: grid; width: 86px; height: 86px; place-items: center; overflow: hidden; margin: 15px auto 12px; border: 4px solid #fffefa; border-radius: 50%; background: #dfe3d2; box-shadow: 0 0 0 1px #dfe1d2, 0 7px 18px rgba(65,76,44,.14); color: var(--editor-olive); font-family: Georgia, serif; font-size: 34px; }
    .lover-preview-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .lover-preview-body h2 { margin: 0; color: var(--editor-deep); font-family: 'Playfair Display', Georgia, serif; font-size: 25px; font-weight: 500; overflow-wrap: anywhere; }
    .lover-preview-role { display: inline-block; margin-top: 6px; color: #81876e; font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .lover-preview-body p { margin: 15px auto; color: #7a806c; font-size: 12px; line-height: 1.7; }
    .lover-preview-rule { display: flex; align-items: center; gap: 10px; margin: 18px 0 13px; color: #b69a64; }
    .lover-preview-rule span { height: 1px; flex: 1; background: #e8e5d8; }
    .lover-preview-rule i { font-size: 12px; font-style: normal; }
    .lover-preview-body > small { color: #939581; font-size: 9px; letter-spacing: .06em; }

    @media (max-width: 850px) {
        .lover-editor-layout { grid-template-columns: minmax(0, 1fr); }
        .lover-editor-preview { position: static; display: grid; grid-template-columns: 150px minmax(0, 1fr); }
        .lover-preview-art { min-height: 100%; align-items: flex-start; }
        .lover-preview-body { padding: 20px; }
    }

    @media (max-width: 560px) {
        .lover-editor { padding: 28px 16px 50px; }
        .lover-editor-heading { margin: 24px 0; }
        .lover-editor-form { padding: 21px 17px; border-radius: 17px; }
        .lover-editor-fields { grid-template-columns: 1fr; gap: 15px; }
        .lover-editor-field-wide { grid-column: auto; }
        .lover-editor-preview { grid-template-columns: 1fr; }
        .lover-preview-art { min-height: 85px; }
        .lover-editor-actions { justify-content: space-between; }
    }

    @media (prefers-reduced-motion: reduce) {
        .lover-editor * { scroll-behavior: auto !important; transition: none !important; }
    }
</style>
@endsection
