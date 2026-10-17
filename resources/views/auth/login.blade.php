
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f3e8">

    <title>Login | Artfy</title>

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
            max-width: 460px;
            padding: 36px;
            background: #fffefa;
            border: 1px solid #e2e1d5;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(65, 76, 44, 0.09);
        }

        .brand {
            display: inline-block;
            margin-bottom: 24px;
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
            margin-bottom: 10px;
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
            margin: 0 0 24px;
            color: #72795f;
            font-size: 14px;
            line-height: 1.8;
        }

        label {
            display: block;
            margin: 17px 0 8px;
            font-size: 13px;
            font-weight: 600;
        }

        input:not([type="checkbox"]),
        select {
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

        input:focus,
        select:focus {
            outline: 2px solid #72795f;
            outline-offset: 2px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-top: 17px;
            font-size: 13px;
        }

        .remember-row input {
            width: 16px;
            height: 16px;
            accent-color: #55613a;
        }

        .remember-row label {
            margin: 0;
            font-weight: 400;
        }

        .login-button {
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

        .login-button:hover {
            background: #414c2c;
        }

        a {
            color: #55613a;
        }

        .message {
            margin: 14px 0;
            padding: 11px 13px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.6;
        }

        .error {
            border: 1px solid #e8b9b4;
            background: #fff5f4;
            color: #b42318;
        }

        .success {
            border: 1px solid #c7ddc9;
            background: #f1f8f0;
            color: #276738;
        }

        .account-links {
            margin-top: 24px;
            font-size: 13px;
            line-height: 1.9;
            text-align: center;
        }

        .account-links p {
            margin: 10px 0;
        }

        .account-links a {
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

        <p class="eyebrow">WELCOME BACK</p>

        <h1>Welcome back to Artfy</h1>

        <p class="intro">
            Log in to discover art, connect with artists, and continue your Artfy journey.
        </p>

        @if (session('success'))
            <div class="message success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="message error" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="message error" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <label for="role">Account type</label>

            <select id="role" name="role" required>
                <option value="art_lover"
                    {{ old('role', $role ?? 'art_lover') === 'art_lover' ? 'selected' : '' }}>
                    Art Lover
                </option>

                <option value="artist"
                    {{ old('role', $role ?? 'art_lover') === 'artist' ? 'selected' : '' }}>
                    Artist
                </option>

                <option value="admin"
                    {{ old('role', $role ?? 'art_lover') === 'admin' ? 'selected' : '' }}>
                    Admin
                </option>
            </select>

            <label for="email">Email address</label>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                required
                autocomplete="email"
            >

            <label for="password">Password</label>

            <input
                id="password"
                type="password"
                name="password"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
            >

            <div class="remember-row">
                <input
                    id="remember"
                    type="checkbox"
                    name="remember"
                    value="1"
                    {{ old('remember') ? 'checked' : '' }}
                >

                <label for="remember">Remember me</label>
            </div>

            <button class="login-button" type="submit">
                Log In
            </button>
        </form>

        <div class="account-links">
            <p>
                Don't have an account?
                <a href="{{ route('auth.choose-role') }}">Join Artfy</a>
            </p>

            <p>
                <a href="{{ route('home') }}">← Back to Artfy</a>
            </p>
        </div>

    </main>
</body>
</html>
