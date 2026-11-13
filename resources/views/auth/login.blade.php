
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f3e8">
    <link rel="icon" type="image/svg+xml" href="{{ asset('artfy-logo.svg') }}?v=3">

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
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            color: #414c2c;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 28px;
            font-weight: 700;
            text-decoration: none;
        }

        .brand img { width: 40px; height: 40px; filter: drop-shadow(0 3px 6px rgba(65, 76, 44, .16)); }

        .brand span {
            color: #72795f;
        }

        .brand .brand-name { color: #414c2c; }

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

        .forgot-password-link{margin:14px 0 0;font-size:13px;text-align:right}
        .forgot-password-link a{font-weight:600;text-underline-offset:3px}
        .forgot-password-link[hidden]{display:none}

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

        body{background:radial-gradient(circle at 10% 10%,#e6e9d7,transparent 38%),radial-gradient(circle at 93% 90%,#eadfbe,transparent 32%),#f5f3e8;color:#35422d}
        .card{position:relative;isolation:isolate;width:min(100%,1040px);max-width:1040px;min-height:610px;margin:24px 0;padding:48px 58px 42px 46%;overflow:hidden;border:1px solid rgba(125,132,92,.24);border-radius:28px;background:rgba(255,254,249,.94);box-shadow:0 35px 90px rgba(65,76,44,.14),0 8px 24px rgba(65,76,44,.06);backdrop-filter:blur(12px)}
        .card::before{position:absolute;z-index:-1;inset:0 auto 0 0;display:flex;width:40%;align-items:flex-end;padding:42px 40px;content:"‘Every work begins\A with a feeling.’\A\A ARTFY  /  CREATIVE COMMUNITY";white-space:pre-line;color:#fffdf3;font:400 clamp(26px,3.4vw,44px)/1.18 Georgia,serif;letter-spacing:-.025em;background:radial-gradient(circle at 74% 21%,rgba(255,255,255,.34) 0 4%,transparent 4.4%),radial-gradient(circle at 74% 21%,transparent 0 18%,rgba(255,255,255,.2) 18.2% 18.5%,transparent 18.8%),radial-gradient(ellipse at 85% 12%,rgba(220,206,162,.8),transparent 34%),linear-gradient(145deg,#87906b,#65734e 52%,#4f6042);box-shadow:inset -1px 0 rgba(255,255,255,.45)}
        .card::after{position:absolute;z-index:-1;top:7%;left:5%;width:29%;aspect-ratio:1;border:1px solid rgba(255,255,255,.26);border-radius:50%;content:"";box-shadow:0 0 0 18px rgba(255,255,255,.08),0 0 0 42px rgba(255,255,255,.045)}
        .brand{margin-bottom:28px;font-size:32px}.eyebrow{color:#7d835d}.intro{color:#737762;line-height:1.75}
        .card h1{color:#39452f;font-size:clamp(30px,4vw,42px);letter-spacing:-.035em}
        label{color:#4c5540;font-size:12px;letter-spacing:.02em}
        input:not([type="checkbox"]),select{min-height:50px;border-color:#d8d9c9;border-radius:12px;background:#fffefa;box-shadow:inset 0 1px 3px rgba(65,76,44,.035)}
        input:focus,select:focus{outline:3px solid rgba(151,160,113,.3);border-color:#87906b}
        .login-button{border-radius:12px;background:linear-gradient(115deg,#788456,#596b43);box-shadow:0 9px 18px rgba(85,97,58,.2);transition:transform .2s,box-shadow .2s,background .2s}
        .login-button:hover{transform:translateY(-2px);background:linear-gradient(115deg,#697849,#4d603b);box-shadow:0 13px 24px rgba(85,97,58,.24)}
        .account-links{color:#747966}.message{border-radius:12px}
        @media(max-width:680px){.card{min-height:0;padding:278px 26px 30px}.card::before{inset:0 0 auto;width:100%;height:245px;padding:28px;align-items:flex-end;font-size:27px}.card::after{top:18px;left:58%;width:35%}}
        @media(max-width:420px){.card{padding:250px 20px 24px}.card::before{height:222px}.brand{margin-bottom:20px}}
    </style>
</head>

<body>
    <main class="card">

        <a class="brand" href="{{ route('home') }}" aria-label="Artfy home">
            <img src="{{ asset('artfy-logo.svg') }}" alt="">
            <span class="brand-name">Art<span>fy.</span></span>
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

                <option value="co_admin"
                    {{ old('role', $role ?? 'art_lover') === 'co_admin' ? 'selected' : '' }}>
                    Exhibition Co-admin
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

        <p id="forgot-password-link" class="forgot-password-link" {{ in_array($role ?? 'art_lover', ['artist', 'art_lover'], true) ? '' : 'hidden' }}>
            <a href="{{ route('password.request') }}">Forgot your password?</a>
        </p>

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
    <script>
        const accountRoleSelect = document.getElementById('role');
        const forgotPasswordLink = document.getElementById('forgot-password-link');
        accountRoleSelect?.addEventListener('change', () => {
            forgotPasswordLink.hidden = !['artist', 'art_lover'].includes(accountRoleSelect.value);
        });
    </script>
</body>
</html>
