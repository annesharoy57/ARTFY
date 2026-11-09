@extends('admin.layout')

@section('title', 'Exhibition Co-admins | ARTFY Admin')
@section('page_eyebrow', 'Artfy / User Management')
@section('page_title', 'Exhibition co-admins')
@section('page_intro', 'Create up to three restricted accounts that can only view exhibitions and score participant artwork.')

@section('content')
    @if (session('success'))
        <p class="coadmin-message success" role="status">{{ session('success') }}</p>
    @endif
    @if ($errors->any())
        <div class="coadmin-message error" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="coadmin-panel">
        <h2>Accounts ({{ $coAdmins->count() }} / {{ $coAdminLimit }})</h2>
        @forelse ($coAdmins as $coAdmin)
            <form method="POST" action="{{ route('admin.co-admins.update', $coAdmin) }}" class="coadmin-edit-form">
                @csrf
                @method('PUT')
                <h3>Edit {{ $coAdmin->name }}</h3>
                <label>Name <input name="name" value="{{ $coAdmin->name }}" required maxlength="255"></label>
                <label>Email <input type="email" name="email" value="{{ $coAdmin->email }}" required maxlength="255"></label>
                <label>New password <input type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Leave blank to keep current password"></label>
                <label>Confirm new password <input type="password" name="password_confirmation" minlength="8" autocomplete="new-password"></label>
                <button type="submit">Save co-admin</button>
            </form>
        @empty
            <p>No co-admin accounts have been created.</p>
        @endforelse
    </section>

    @if ($coAdmins->count() < $coAdminLimit)
        <section class="coadmin-panel">
            <h2>Create a co-admin account</h2>
            <form method="POST" action="{{ route('admin.co-admins.store') }}">
                @csrf
                <label>Name <input name="name" value="{{ old('name') }}" required maxlength="255"></label>
                <label>Email <input type="email" name="email" value="{{ old('email') }}" required maxlength="255"></label>
                <label>Password <input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
                <label>Confirm password <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>
                <button type="submit">Create co-admin</button>
            </form>
        </section>
    @else
        <p>The maximum of three co-admin accounts has been reached.</p>
    @endif

    <style>
        .coadmin-panel{margin:18px 0;padding:22px;border:1px solid var(--border);border-radius:10px;background:#fff}
        .coadmin-panel h2{margin-top:0}.coadmin-panel form{display:grid;gap:14px;max-width:560px}
        .coadmin-panel .coadmin-edit-form{margin:20px 0;padding:18px 0;border-top:1px solid var(--border)}
        .coadmin-edit-form h3{margin:0}
        .coadmin-panel label{display:grid;gap:6px}.coadmin-panel input{padding:10px;border:1px solid var(--border);border-radius:6px;font:inherit}
        .coadmin-panel button{width:max-content;padding:11px 16px;border:0;border-radius:6px;background:var(--dark-olive);color:#fff;font:inherit;font-weight:700;cursor:pointer}
        .coadmin-message{padding:12px 16px;border-radius:8px}.coadmin-message.success{background:var(--success)}.coadmin-message.error{background:var(--danger)}
    </style>
@endsection
