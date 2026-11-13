@extends('admin.layout')

@section('title', 'Exhibition Co-admins | ARTFY Admin')
@section('page_eyebrow', 'Artfy / User Management')
@section('page_title', 'Co-admin studio')
@section('page_intro', 'Manage the judges who help review exhibition entries and score participating artwork.')

@section('content')
    @if (session('success'))
        <div class="coadmin-alert is-success" role="status"><span class="alert-mark">✓</span><span>{{ session('success') }}</span></div>
    @endif
    @if ($errors->any())
        <div class="coadmin-alert is-error" role="alert">
            <strong>We couldn’t save those changes.</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="coadmin-hero" aria-label="Co-admin overview">
        <div class="hero-orbit orbit-one"></div><div class="hero-orbit orbit-two"></div>
        <div class="hero-copy">
            <span class="hero-kicker">THE JUDGING TEAM</span>
            <h2>Good art deserves<br><em>a thoughtful eye.</em></h2>
            <p>Co-admins can review exhibition artwork and submit scores. Their accounts are kept separate from the main admin tools.</p>
        </div>
        <div class="hero-stat" aria-label="{{ $coAdmins->count() }} of {{ $coAdminLimit }} co-admin seats in use">
            <span class="stat-number">{{ str_pad((string) $coAdmins->count(), 2, '0', STR_PAD_LEFT) }}<small> / {{ str_pad((string) $coAdminLimit, 2, '0', STR_PAD_LEFT) }}</small></span>
            <span class="stat-caption">JUDGE SEATS FILLED</span>
            <div class="seat-track" aria-hidden="true"><i style="width: {{ min(100, ($coAdmins->count() / max(1, $coAdminLimit)) * 100) }}%"></i></div>
            <span class="seat-note">{{ $coAdminLimit - $coAdmins->count() }} {{ Str::plural('seat', $coAdminLimit - $coAdmins->count()) }} available</span>
        </div>
        <span class="hero-spark spark-one">✳</span><span class="hero-spark spark-two">✦</span>
    </section>

    <div class="coadmin-columns">
        <section class="coadmin-section roster-section">
            <div class="section-heading">
                <div><span class="section-kicker">YOUR PANEL</span><h2>Judges & access</h2></div>
                <span class="section-count">{{ $coAdmins->count() }} / {{ $coAdminLimit }}</span>
            </div>

            @forelse ($coAdmins as $coAdmin)
                <article class="judge-card">
                    <div class="judge-topline">
                        <div class="judge-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($coAdmin->name, 0, 1)) }}</div>
                        <div class="judge-identity"><span class="judge-label">CO-ADMIN · JUDGE {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $coAdmin->name }}</h3><p>{{ $coAdmin->email }}</p></div>
                        <span class="access-pill"><i></i> Active access</span>
                    </div>
                    <details class="judge-edit">
                        <summary><span>Edit account</span><span class="edit-chevron">⌄</span></summary>
                        <form method="POST" action="{{ route('admin.co-admins.update', $coAdmin) }}" class="coadmin-form">
                            @csrf
                            @method('PUT')
                            <div class="form-grid">
                                <label>Name<input name="name" value="{{ old('name', $coAdmin->name) }}" required maxlength="255"></label>
                                <label>Email address<input type="email" name="email" value="{{ old('email', $coAdmin->email) }}" required maxlength="255"></label>
                                <label>New password <span class="optional">OPTIONAL</span><input type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Leave blank to keep current"></label>
                                <label>Confirm new password<input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" placeholder="Repeat new password"></label>
                            </div>
                            <button class="primary-button" type="submit">Save account <span aria-hidden="true">→</span></button>
                        </form>
                    </details>
                </article>
            @empty
                <div class="empty-roster"><span class="empty-icon">✳</span><h3>Your judging panel is ready</h3><p>Create the first co-admin account to begin assigning exhibition judges.</p></div>
            @endforelse
        </section>

        <aside class="create-card">
            @if ($coAdmins->count() < $coAdminLimit)
                <div class="create-heading"><span class="create-icon">＋</span><div><span class="section-kicker">ADD A JUDGE</span><h2>Invite a co-admin</h2></div></div>
                <p class="create-intro">Create a secure sign-in for someone helping score exhibition artwork.</p>
                <form method="POST" action="{{ route('admin.co-admins.store') }}" class="coadmin-form create-form">
                    @csrf
                    <label>Full name<input name="name" value="{{ old('name') }}" required maxlength="255" placeholder="e.g. Amina Rahman"></label>
                    <label>Email address<input type="email" name="email" value="{{ old('email') }}" required maxlength="255" placeholder="judge@example.com"></label>
                    <label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters"></label>
                    <label>Confirm password<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Enter it again"></label>
                    <button class="primary-button create-submit" type="submit">Create co-admin <span aria-hidden="true">→</span></button>
                </form>
                <div class="permission-note"><span>✦</span><p><strong>Limited permissions</strong><br>This account can view exhibition entries and submit scores.</p></div>
            @else
                <div class="limit-reached"><span class="create-icon">✓</span><span class="section-kicker">PANEL COMPLETE</span><h2>All judge seats are filled</h2><p>The maximum of {{ $coAdminLimit }} co-admin accounts has been reached. You can still edit the existing accounts.</p></div>
            @endif
        </aside>
    </div>

    <style>
        .coadmin-hero{position:relative;isolation:isolate;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:32px;min-height:250px;margin:26px 0 30px;padding:38px 46px;border-radius:24px;background:linear-gradient(115deg,#414c2c 0%,#53613a 66%,#68744d 100%);color:#fffdf5;box-shadow:0 20px 45px rgba(52,61,37,.16)}
        .hero-copy{position:relative;z-index:1;max-width:610px}.hero-kicker,.section-kicker{display:block;font-size:10px;font-weight:800;letter-spacing:2px}.hero-kicker{color:#d9c38e}.hero-copy h2{margin:13px 0 10px;font:normal clamp(32px,4vw,48px)/1.06 Georgia,serif;letter-spacing:-1px}.hero-copy h2 em{color:#e1c183;font-weight:normal}.hero-copy p{max-width:510px;margin:0;color:#e2e3d6;font-size:14px;line-height:1.75}
        .hero-stat{position:relative;z-index:1;flex:0 0 205px;padding:22px 22px 20px;border:1px solid rgba(255,255,255,.22);border-radius:17px;background:rgba(255,255,255,.09);backdrop-filter:blur(10px)}.stat-number{display:block;font:44px/1 Georgia,serif}.stat-number small{color:#d8d9cc;font:18px Arial,sans-serif}.stat-caption{display:block;margin-top:9px;color:#e4d5ad;font-size:9px;font-weight:800;letter-spacing:1.5px}.seat-track{height:5px;margin:17px 0 9px;border-radius:8px;background:rgba(255,255,255,.2);overflow:hidden}.seat-track i{display:block;height:100%;border-radius:8px;background:#e2c182}.seat-note{color:#e3e4d8;font-size:11px}.hero-orbit{position:absolute;z-index:-1;border:1px solid rgba(255,255,255,.12);border-radius:50%;pointer-events:none}.orbit-one{width:380px;height:380px;right:11%;top:-230px}.orbit-two{width:290px;height:290px;right:14%;top:-185px}.hero-spark{position:absolute;color:#e2c182;opacity:.8}.spark-one{right:33%;top:37px;font-size:23px}.spark-two{right:7%;bottom:34px;font-size:15px}
        .coadmin-columns{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(300px,.8fr);align-items:start;gap:24px}.coadmin-section,.create-card{border:1px solid var(--border);border-radius:20px;background:rgba(255,255,255,.78);box-shadow:0 12px 32px rgba(56,56,42,.045)}.coadmin-section{padding:24px}.section-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:17px}.section-kicker{color:#878a70}.section-heading h2,.create-heading h2{margin:5px 0 0;color:#303727;font:normal 26px/1.15 Georgia,serif}.section-count{display:grid;place-items:center;min-width:45px;height:36px;padding:0 11px;border:1px solid #e7e2d2;border-radius:30px;background:#faf8f0;color:#53613a;font-size:12px;font-weight:800}
        .judge-card{margin-top:13px;padding:17px 18px;border:1px solid #eae6da;border-radius:15px;background:linear-gradient(145deg,#fffefa,#f8f6ee);transition:transform .2s ease,box-shadow .2s ease}.judge-card:hover{transform:translateY(-2px);box-shadow:0 12px 24px rgba(56,56,42,.07)}.judge-topline{display:flex;align-items:center;gap:13px}.judge-avatar{display:grid;place-items:center;flex:0 0 48px;height:48px;border:1px solid #dfd5b8;border-radius:50%;background:linear-gradient(145deg,#e2c994,#b9c09b);color:#465132;font:21px Georgia,serif}.judge-identity{min-width:0;flex:1}.judge-label{color:#8b8a70;font-size:9px;font-weight:800;letter-spacing:1.3px}.judge-identity h3{margin:4px 0 2px;color:#333b29;font-size:16px}.judge-identity p{overflow:hidden;margin:0;color:#828276;font-size:12px;text-overflow:ellipsis;white-space:nowrap}.access-pill{display:inline-flex;align-items:center;gap:6px;padding:7px 9px;border-radius:30px;background:#edf3e7;color:#596b43;font-size:10px;font-weight:700;white-space:nowrap}.access-pill i{width:6px;height:6px;border-radius:50%;background:#769052}
        .judge-edit{margin:15px -18px -17px;border-top:1px solid #eeeadf}.judge-edit summary{display:flex;align-items:center;justify-content:space-between;padding:12px 18px;color:#596b43;font-size:12px;font-weight:700;cursor:pointer;list-style:none}.judge-edit summary::-webkit-details-marker{display:none}.edit-chevron{font-size:18px;transition:transform .2s}.judge-edit[open] .edit-chevron{transform:rotate(180deg)}.judge-edit .coadmin-form{padding:3px 18px 18px}
        .create-card{padding:25px;background:linear-gradient(160deg,#fffef9,#f1efe4)}.create-heading{display:flex;align-items:center;gap:13px}.create-icon{display:grid;place-items:center;flex:0 0 43px;height:43px;border-radius:14px;background:#e8e8d9;color:#53613a;font-size:26px}.create-intro{margin:14px 0 20px;color:#77786e;font-size:13px;line-height:1.65}.coadmin-form{display:grid;gap:14px}.coadmin-form label{display:grid;gap:7px;color:#515344;font-size:11px;font-weight:700}.coadmin-form input{width:100%;min-height:43px;padding:11px 12px;border:1px solid #ddd9cc;border-radius:10px;outline:none;background:#fffefa;color:#303127;font:13px Arial,sans-serif;transition:border-color .2s,box-shadow .2s}.coadmin-form input:focus{border-color:#879369;box-shadow:0 0 0 3px rgba(111,128,79,.13)}.coadmin-form input::placeholder{color:#aaa99b;font-weight:400}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:13px}.optional{margin-left:4px;color:#a09b85;font-size:8px;letter-spacing:.8px}.primary-button{display:inline-flex;align-items:center;justify-content:center;gap:13px;width:max-content;min-height:43px;padding:0 17px;border:0;border-radius:10px;background:#566541;color:#fffdf6;font-size:12px;font-weight:700;cursor:pointer;transition:transform .2s,background .2s,box-shadow .2s}.primary-button:hover{transform:translateY(-1px);background:#414c2c;box-shadow:0 8px 18px rgba(65,76,44,.2)}.primary-button span{font-size:17px}.create-submit{width:100%;margin-top:4px}.permission-note{display:flex;gap:10px;margin-top:20px;padding:13px;border:1px solid #e6e2d4;border-radius:12px;background:rgba(255,255,255,.55);color:#7a7b6f}.permission-note>span{color:#aa8c50}.permission-note p{margin:0;font-size:11px;line-height:1.55}.permission-note strong{color:#4a513d}.coadmin-alert{display:flex;gap:10px;align-items:flex-start;margin:0 0 16px;padding:13px 16px;border-radius:12px;font-size:13px}.coadmin-alert.is-success{border:1px solid #c8dec5;background:#eaf4e8;color:#3c633c}.coadmin-alert.is-error{display:block;border:1px solid #ecd0ca;background:#fff0ed;color:#8b3f32}.coadmin-alert ul{margin:6px 0 0;padding-left:20px}.alert-mark{font-weight:800}.empty-roster{padding:34px 20px;border:1px dashed #d9d5c7;border-radius:15px;text-align:center;background:#fbfaf5}.empty-icon{display:inline-grid;place-items:center;width:44px;height:44px;border-radius:50%;background:#eeeddf;color:#76815d;font-size:22px}.empty-roster h3{margin:12px 0 5px;color:#39422d;font:normal 20px Georgia,serif}.empty-roster p,.limit-reached p{max-width:330px;margin:0 auto;color:#7e7e72;font-size:12px;line-height:1.6}.limit-reached{text-align:center;padding:22px 5px}.limit-reached .create-icon{margin:0 auto 15px}.limit-reached h2{margin:9px 0 10px;color:#303727;font:normal 25px Georgia,serif}
        @media(max-width:980px){.coadmin-columns{grid-template-columns:1fr}.create-card{order:-1}.hero-stat{flex-basis:175px}.coadmin-hero{padding:32px}}
        @media(max-width:600px){.coadmin-hero{align-items:flex-start;flex-direction:column;gap:22px;padding:27px 23px;border-radius:18px}.hero-stat{width:100%;flex-basis:auto}.hero-copy h2{font-size:36px}.coadmin-section,.create-card{padding:19px;border-radius:16px}.judge-topline{flex-wrap:wrap}.access-pill{margin-left:60px}.form-grid{grid-template-columns:1fr}.section-heading h2,.create-heading h2{font-size:23px}}
        @media(prefers-reduced-motion:reduce){.judge-card,.primary-button,.coadmin-form input{transition:none}}
    </style>
@endsection
