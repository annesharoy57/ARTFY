<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f3e8">
    <title>@yield('title', 'Password recovery') | Artfy</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box}
        body{display:grid;min-height:100vh;place-items:center;margin:0;padding:24px;background:radial-gradient(circle at 12% 12%,#e6e9d7,transparent 38%),radial-gradient(circle at 90% 88%,#eadfbe,transparent 32%),#f5f3e8;color:#414c2c;font-family:"DM Sans",Arial,sans-serif}
        .recovery-card{width:min(100%,520px);padding:clamp(28px,6vw,48px);border:1px solid rgba(125,132,92,.24);border-radius:24px;background:rgba(255,254,249,.95);box-shadow:0 28px 76px rgba(65,76,44,.14);backdrop-filter:blur(12px)}
        .brand{display:inline-block;margin-bottom:28px;color:#414c2c;font:700 30px "Playfair Display",Georgia,serif;text-decoration:none}.brand span{color:#788456}
        .eyebrow{margin:0 0 10px;color:#858966;font-size:.7rem;font-weight:800;letter-spacing:.18em}
        h1{margin:0 0 12px;color:#39452f;font:500 clamp(2rem,7vw,2.7rem)/1.1 "Playfair Display",Georgia,serif}
        .intro{margin:0 0 24px;color:#737762;font-size:.94rem;line-height:1.7}
        label{display:block;margin:18px 0 8px;color:#4c5540;font-size:.82rem;font-weight:700}
        input{display:block;width:100%;min-height:50px;padding:12px 14px;border:1px solid #d1d4c3;border-radius:12px;background:#fffefa;color:#414c2c;font:inherit}
        input:focus{outline:3px solid rgba(151,160,113,.3);border-color:#87906b}
        button{width:100%;margin-top:20px;padding:14px;border:0;border-radius:12px;background:linear-gradient(115deg,#788456,#596b43);box-shadow:0 9px 18px rgba(85,97,58,.18);color:#fff;font:700 .95rem "DM Sans",Arial,sans-serif;cursor:pointer;transition:transform .2s,box-shadow .2s}
        button:hover{transform:translateY(-2px);box-shadow:0 13px 24px rgba(85,97,58,.24)}
        a{color:#596b43;text-underline-offset:3px}.back-link{display:block;margin-top:22px;font-size:.86rem;text-align:center}
        .message{margin:14px 0;padding:12px 14px;border:1px solid #c7ddc9;border-radius:12px;background:#f1f8f0;color:#276738;font-size:.84rem;line-height:1.6}
        .errors{margin:14px 0;padding:12px 14px;border:1px solid #e8b9b4;border-radius:12px;background:#fff5f4;color:#a12d25;font-size:.84rem;line-height:1.6}
        @media(max-width:480px){body{padding:14px}.recovery-card{border-radius:19px}}
    </style>
</head>
<body>
    <main class="recovery-card">
        <a class="brand" href="{{ route('home') }}">Art<span>fy.</span></a>
        <p class="eyebrow">ACCOUNT SECURITY</p>
        <h1>@yield('heading', 'Recover your password')</h1>
        <p class="intro">@yield('intro')</p>
        @if (session('status'))
            <div class="message" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="errors" role="alert">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        @yield('recovery-form')
        <a class="back-link" href="{{ route('login') }}">Back to login</a>
    </main>
</body>
</html>
