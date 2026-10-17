@extends('artfy.layout')

@section('content')
<section class="role-page">
    <div class="role-container">

        <p class="small-title">WELCOME TO ARTFY</p>

        <h1>Art connects us.<br>
            <em>Find your place.</em>
        </h1>

        <p class="role-description">
            Whether you create art or simply love discovering it,
            there is a place for you in our creative community.
        </p>

        <div class="role-cards">

            <a href="{{ route('register', ['role' => 'artist']) }}"
               class="role-card artist-role">
                <span class="role-number">01 / CREATE</span>

                <div class="role-art artist-role-art">
                    <span>✳</span>
                </div>

                <div class="role-card-content">
                    <p class="role-label">FOR CREATORS</p>
                    <h2>I'm an Artist</h2>
                    <p>
                        Share your work, build your portfolio,
                        join exhibitions, and connect with art lovers.
                    </p>
                    <span class="role-link">Join as an artist ↗</span>
                </div>
            </a>

            <a href="{{ route('register', ['role' => 'art_lover']) }}"
               class="role-card lover-role">
                <span class="role-number">02 / DISCOVER</span>

                <div class="role-art lover-role-art">
                    <span>♡</span>
                </div>

                <div class="role-card-content">
                    <p class="role-label">FOR ART LOVERS</p>
                    <h2>I'm an Art Lover</h2>
                    <p>
                        Discover beautiful artwork, follow artists,
                        save favorites, and find pieces you love.
                    </p>
                    <span class="role-link">Join for free ↗</span>
                </div>
            </a>

        </div>

        <p class="role-login">
            Already part of Artfy?
            <a href="{{ route('login') }}">Log in to your account</a>
        </p>

    </div>
</section>
@endsection
