
@extends('artfy.layout')

@section('content')
<section style="max-width:900px;margin:60px auto;padding:0 24px;color:#414c2c;">

    @if(session('success'))
        <div style="padding:14px 18px;margin-bottom:20px;border-radius:10px;background:#e5eadb;color:#414c2c;">
            {{ session('success') }}
        </div>
    @endif

    <div style="padding:40px;border-radius:18px;background:#eeeee4;text-align:center;">

        @if($user->profile_photo)
            <img
                src="{{ asset('storage/' . $user->profile_photo) }}"
                alt="Profile photo"
                style="width:110px;height:110px;margin:0 auto 20px;border-radius:50%;object-fit:cover;display:block;"
            >
        @else
            <div style="width:110px;height:110px;margin:0 auto 20px;border-radius:50%;background:#dfe2d2;display:flex;align-items:center;justify-content:center;font-size:42px;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        @endif

        <p style="font-size:12px;letter-spacing:2px;color:#72795f;">
            ARTFY · ART LOVER
        </p>

        <h1 style="font-family:'Playfair Display',serif;margin:12px 0;">
            {{ $user->name }}
        </h1>

        <p>{{ $user->email }}</p>

        @if($user->phone)
            <p>Phone: {{ $user->phone }}</p>
        @endif

        @if($user->address)
            <p>Address: {{ $user->address }}</p>
        @endif

        <p style="max-width:520px;margin:20px auto;line-height:1.8;color:#72795f;">
            Welcome to your Artfy profile. Discover artworks, explore artists,
            and save your favorite pieces.
        </p>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:24px;">

            <a href="{{ route('my-profile.edit') }}"
               style="display:inline-block;padding:12px 22px;background:#55613a;color:white;border-radius:8px;text-decoration:none;">
                Edit Profile
            </a>

            <a href="{{ route('home') }}"
               style="display:inline-block;padding:12px 22px;border:1px solid #55613a;color:#414c2c;border-radius:8px;text-decoration:none;">
                Explore Art
            </a>

        </div>
    </div>

    <div style="margin-top:40px;">
        <h2 style="font-family:'Playfair Display',serif;font-size:28px;">
            Your Art
        </h2>

        <p style="color:#72795f;line-height:1.8;">
            Artworks you purchase will appear here.
        </p>
    </div>

</section>
@endsection
