
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f3e8">

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
            background: #f5f3e8;
            color: #414c2c;
            font-family: "DM Sans", Arial, sans-serif;
        }

        .card {
            width: 100%;
            max-width: 470px;
            padding: 36px;
            background: #fffefa;
            border: 1px solid #e2e1d5;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(65, 76, 44, 0.09);
        }

        .brand {
            display: inline-block;
            margin-bottom: 22px;
            color: #414c2c;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 28px;
            font-weight: 700;
            text-decoration: none;
        }

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
            font-size: clamp(29px, 5vw, 36px);
            font-weight: 500;
        }

        .intro {
            margin: 0 0 22px;
            color: #72795f;
            font-size: 14px;
            line-height: 1.8;
        }

        label {
            display: block;
            margin: 16px 0 7px;
            font-size: 13px;
            font-weight: 600;
        }

        input:not([type="checkbox"]) {
            display: block;
            width: 100%;
            min-height: 46px;
            padding: 12px;
            border: 1px solid #c9cbbd;
            border-radius: 8px;
            background: white;
            color: #414c2c;
            font: inherit;
            font-size: 14px;
        }

        input:focus {
            outline: 2px solid #72795f;
            outline-offset: 2px;
        }

        .error {
            margin: 15px 0;
            padding: 10px 14px;
            border: 1px solid #e8b9b4;
            border-radius: 8px;
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
            margin-top: 24px;
            padding: 14px;
            border: 0;
            border-radius: 8px;
            background: #55613a;
            color: white;
            font: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .submit-button:hover {
            background: #414c2c;
        }

        .account-links {
            margin-top: 22px;
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

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .card {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>
    <main class="card">

        <a class="brand" href="{{ route('home') }}" aria-label="Artfy home">
            Art<span>fy.</span>
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
