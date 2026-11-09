@extends('coadmin.layout')

@section('title', 'Online Exhibitions | Artfy Co-admin')

@section('content')
    <p style="color:#72795f;letter-spacing:2px;font-size:12px;font-weight:700">JUDGING WORKSPACE</p>
    <h1>Online Exhibitions</h1>
    <p>Open an exhibition to review participant artwork and submit your 1–10 scores while visiting is open.</p>

    <div style="display:grid;gap:14px;margin-top:24px">
        @forelse ($exhibitions as $exhibition)
            <article style="padding:20px;border:1px solid #e5e2d6;border-radius:12px;background:#fff">
                <h2 style="margin:0 0 8px">{{ $exhibition->title }}</h2>
                <p>{{ $exhibition->starts_at->format('M j, Y g:i A') }} – {{ $exhibition->ends_at->format('M j, Y g:i A') }}</p>
                <p>{{ $exhibition->participant_count }} participant artworks</p>
                <a href="{{ route('co-admin.exhibitions.show', $exhibition) }}">View exhibition artwork →</a>
            </article>
        @empty
            <p>No published exhibitions are available.</p>
        @endforelse
    </div>
@endsection
