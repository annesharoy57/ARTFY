
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f3e8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('artfy-logo.svg') }}?v=3">

    <title>Join Artfy | Register</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: radial-gradient(circle at 10% 10%, #e6e9d7, transparent 38%), radial-gradient(circle at 93% 90%, #eadfbe, transparent 32%), #f5f3e8;
            color: #35422d;
            font-family: "DM Sans", Arial, sans-serif;
        }

        .card {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            width: min(100%, 1080px);
            min-height: 720px;
            margin: 24px 0;
            padding: 43px 58px 36px 46%;
            border: 1px solid rgba(125, 132, 92, .24);
            border-radius: 28px;
            background: rgba(255, 254, 249, .96);
            box-shadow: 0 35px 90px rgba(65, 76, 44, .14), 0 8px 24px rgba(65, 76, 44, .06);
            backdrop-filter: blur(12px);
        }

        .card::before {
            position: absolute;
            z-index: -1;
            inset: 0 auto 0 0;
            display: flex;
            width: 40%;
            align-items: flex-end;
            padding: 42px 38px;
            content: "Every artist begins\A with a first step.\A\A ARTFY  /  CREATIVE COMMUNITY";
            white-space: pre-line;
            color: #fffdf3;
            font: 400 clamp(27px, 3.5vw, 45px)/1.16 Georgia, serif;
            letter-spacing: -.025em;
            background: radial-gradient(circle at 74% 21%, rgba(255,255,255,.34) 0 4%, transparent 4.4%), radial-gradient(circle at 74% 21%, transparent 0 18%, rgba(255,255,255,.2) 18.2% 18.5%, transparent 18.8%), radial-gradient(ellipse at 85% 12%, rgba(220,206,162,.8), transparent 34%), linear-gradient(145deg,#87906b,#65734e 52%,#4f6042);
            box-shadow: inset -1px 0 rgba(255,255,255,.45);
        }

        .card::after {
            position: absolute;
            z-index: -1;
            top: 7%;
            left: 5%;
            width: 29%;
            aspect-ratio: 1;
            border: 1px solid rgba(255,255,255,.26);
            border-radius: 50%;
            content: "";
            box-shadow: 0 0 0 18px rgba(255,255,255,.08), 0 0 0 42px rgba(255,255,255,.045);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            color: #414c2c;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 28px;
            font-weight: 700;
            text-decoration: none;
        }

        .brand img { width: 40px; height: 40px; filter: drop-shadow(0 3px 6px rgba(65, 76, 44, .16)); }

        .brand .brand-name { color: #414c2c; }

        .brand span {
            color: #72795f;
        }

        .eyebrow {
            margin: 0 0 10px;
            color: #72795f;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        h1 {
            margin: 0 0 12px;
            font-family: "Playfair Display", Georgia, serif;
            color: #39452f;
            font-size: clamp(30px, 4vw, 42px);
            font-weight: 500;
            letter-spacing: -.035em;
        }

        .intro {
            margin: 0 0 18px;
            color: #737762;
            font-size: 14px;
            line-height: 1.75;
        }

        label {
            display: block;
            margin: 13px 0 7px;
            color: #4c5540;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .02em;
        }

        input:not([type="checkbox"]) {
            display: block;
            width: 100%;
            min-height: 48px;
            padding: 12px;
            border: 1px solid #d8d9c9;
            border-radius: 11px;
            background: #fffefa;
            color: #414c2c;
            font: inherit;
            font-size: 14px;
            box-shadow: inset 0 1px 3px rgba(65,76,44,.035);
            transition: border-color .2s, box-shadow .2s;
        }

        input:focus {
            outline: 3px solid rgba(151,160,113,.3);
            border-color: #87906b;
        }

        .error {
            margin: 15px 0;
            padding: 10px 14px;
            border: 1px solid #e8b9b4;
            border-radius: 12px;
            background: #fff5f4;
            color: #b42318;
            font-size: 13px;
            line-height: 1.6;
        }

        .error p {
            margin: 5px 0;
        }

        .submit-button {
            width: 100%;
            margin-top: 20px;
            padding: 14px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(115deg,#788456,#596b43);
            color: white;
            font: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 9px 18px rgba(85,97,58,.2);
            transition: transform .2s, box-shadow .2s, background .2s;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            background: linear-gradient(115deg,#697849,#4d603b);
            box-shadow: 0 13px 24px rgba(85,97,58,.24);
        }

        .account-links {
            margin-top: 16px;
            color: #747966;
            font-size: 13px;
            line-height: 1.9;
            text-align: center;
        }

        .account-links p {
            margin: 10px 0;
        }

        .account-links a {
            color: #55613a;
            font-weight: 600;
            text-underline-offset: 3px;
        }

        @media(max-width:680px){
            body{padding:15px;align-items:start}
            .card{min-height:0;margin:10px 0;padding:278px 28px 30px}
            .card::before{inset:0 0 auto;width:100%;height:245px;padding:28px;align-items:flex-end;font-size:28px}
            .card::after{top:18px;left:58%;width:35%}
        }
        @media(max-width:420px){.card{padding:250px 21px 25px;border-radius:22px}.card::before{height:222px;font-size:26px}.brand{margin-bottom:19px}}
        @media(prefers-reduced-motion:reduce){.submit-button,input:not([type="checkbox"]){transition:none}}
    </style>
</head>

<body>
    <main class="card">

        <a class="brand" href="{{ route('home') }}" aria-label="Artfy home">
            <img src="{{ asset('artfy-logo.svg') }}" alt="">
            <span class="brand-name">Art<span>fy.</span></span>
        </a>

        <p class="eyebrow">JOIN THE ART COMMUNITY</p>

        <h1>
            {{ ($role ?? 'art_lover') === 'artist'
                ? 'Join as an Artist'
                : 'Join Artfy' }}
        </h1>

        <p class="intro">
            {{ ($role ?? 'art_lover') === 'artist'
                ? 'Create your account and begin the artist membership application process.'
                : 'Create your free art-lover account and discover a world of creativity.' }}
        </p>

        @if ($errors->any())
            <div class="error" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}">
            @csrf

            {{-- The controller validates this value server-side. --}}
            <input
                type="hidden"
                name="role"
                value="{{ old('role', $role ?? 'art_lover') }}"
            >

            <label for="name">Full name</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                placeholder="Enter your full name"
                required
                maxlength="255"
                autocomplete="name"
            >

            <label for="email">Email address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                required
                maxlength="255"
                autocomplete="email"
            >

            <label for="password">Password (at least 8 characters)</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                minlength="8"
                autocomplete="new-password"
            >

            <label for="password_confirmation">Confirm password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                minlength="8"
                autocomplete="new-password"
            >

            <button class="submit-button" type="submit">
                {{ ($role ?? 'art_lover') === 'artist'
                    ? 'Create Artist Account'
                    : 'Create Free Account' }}
            </button>
        </form>

        <div class="account-links">
            <p>
                Already have an account?
                <a href="{{ route('login', ['role' => $role ?? 'art_lover']) }}">
                    Log in
                </a>
            </p>

            <p>
                <a href="{{ route('auth.choose-role') }}">
                    ← Choose account type
                </a>
            </p>

            <p>
                <a href="{{ route('home') }}">Back to Discover</a>
            </p>
        </div>

    </main>
</body>
</html>
