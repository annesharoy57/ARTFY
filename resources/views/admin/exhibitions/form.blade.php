@extends('admin.layout')

@php($isEditing = $exhibition->exists)

@section('title', ($isEditing ? 'Edit' : 'Create') . ' Exhibition | ARTFY Admin')
@section('page_eyebrow', 'Artfy / Exhibition Management')
@section('page_title', $isEditing ? 'Edit exhibition' : 'Create exhibition')
@section('page_intro', 'Set the public details, registration window, artist capacity, and fixed BDT registration fee.')

@section('content')
    <form class="exhibition-form" method="POST" action="{{ $isEditing ? route('admin.exhibitions.update', $exhibition) : route('admin.exhibitions.store') }}">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        @foreach ([
            ['title', 'Title', 'text'],
            ['subtitle', 'Subtitle (optional)', 'text'],
        ] as [$name, $label, $type])
            <label>{{ $label }}
                <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $exhibition->{$name}) }}" @if ($name === 'title') required @endif>
                @error($name)<small>{{ $message }}</small>@enderror
            </label>
        @endforeach

        <label>Description
            <textarea name="description" rows="6" required>{{ old('description', $exhibition->description) }}</textarea>
            @error('description')<small>{{ $message }}</small>@enderror
        </label>

        @foreach ([
            ['registration_opens_at', 'Applications open'],
            ['registration_closes_at', 'Application deadline'],
            ['starts_at', 'Exhibition starts'],
            ['ends_at', 'Exhibition ends'],
        ] as [$name, $label])
            <label>{{ $label }}
                <input type="datetime-local" name="{{ $name }}" value="{{ old($name, $exhibition->{$name}?->format('Y-m-d\TH:i')) }}" required>
                @error($name)<small>{{ $message }}</small>@enderror
            </label>
        @endforeach

        <label>Maximum artists
            <input type="number" min="1" max="10000" name="max_artists" value="{{ old('max_artists', $exhibition->max_artists) }}" required>
            @error('max_artists')<small>{{ $message }}</small>@enderror
        </label>

        <label>Registration fee (BDT)
            <input type="number" min="0.01" step="0.01" name="registration_fee" value="{{ old('registration_fee', $exhibition->registration_fee) }}" required>
            @error('registration_fee')<small>{{ $message }}</small>@enderror
        </label>

        <label>Visibility
            <select name="status" required>
                <option value="draft" @selected(old('status', $exhibition->status ?: 'draft') === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $exhibition->status) === 'published')>Published</option>
            </select>
            @error('status')<small>{{ $message }}</small>@enderror
        </label>

        <div class="exhibition-form-actions">
            <button type="submit">{{ $isEditing ? 'Save changes' : 'Create exhibition' }}</button>
            <a href="{{ route('admin.exhibitions.index') }}">Cancel</a>
        </div>
    </form>

    <style>
        .exhibition-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;max-width:900px;padding:22px;border:1px solid var(--border);border-radius:12px;background:#fff}
        .exhibition-form label{display:grid;gap:7px;font-size:14px;font-weight:600}
        .exhibition-form label:has(textarea){grid-column:1/-1}
        .exhibition-form input,.exhibition-form textarea,.exhibition-form select{width:100%;padding:11px;border:1px solid var(--border);border-radius:7px;background:#fff;font:inherit}
        .exhibition-form small{color:#922f32;font-weight:400}
        .exhibition-form-actions{grid-column:1/-1;display:flex;align-items:center;gap:14px}
        .exhibition-form-actions button{padding:12px 16px;border:0;border-radius:7px;background:#414c2c;color:#fff;font-weight:700;cursor:pointer}
        .exhibition-form-actions a{color:var(--olive)}
        @media(max-width:650px){.exhibition-form{grid-template-columns:1fr}.exhibition-form label:has(textarea),.exhibition-form-actions{grid-column:auto}}
    </style>
@endsection
