
@extends('artfy.layout')

@section('content')
<section style="max-width:900px;margin:60px auto;padding:0 24px;color:#414c2c;">
    <div style="
        padding:40px;
        border-radius:18px;
        background:#eeeee4;
        text-align:center;
    ">
        <div style="
            width:110px;
            height:110px;
            margin:0 auto 20px;
            border-radius:50%;
            background:#dfe2d2;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:42px;
        ">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>

        <h1 style="font-family:'Playfair Display',serif;">
            {{ $user->name }}
        </h1>

        <p>{{ $user->email }}</p>

        <p>
            Welcome to your Artfy profile. Discover artworks and save
            your favorite pieces.
        </p>

        <a href="{{ route('home') }}"
           style="
                display:inline-block;
                padding:12px 20px;
                background:#55613a;
                color:white;
                border-radius:8px;
                text-decoration:none;
           ">
            Explore Art
        </a>
    </div>
</section>
@endsection
