<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#414c2c">
    <link rel="icon" type="image/svg+xml" href="{{ asset('artfy-logo.svg') }}?v=3">
    <link rel="alternate icon" href="{{ asset('artfy-logo.svg') }}?v=3">
    <title>@yield('title', 'Exhibition Co-admin | Artfy')</title>
    <style>
        :root{font-family:Arial,sans-serif;color:#292d22;background:#f8f6f2}
        *{box-sizing:border-box}body{margin:0}.topbar{position:relative;z-index:2;display:flex;justify-content:space-between;align-items:center;gap:24px;padding:13px max(24px,calc((100% - 1280px)/2));border-bottom:1px solid rgba(255,255,255,.12);background:linear-gradient(105deg,#384326,#4a5633 70%,#596542);color:#fff;box-shadow:0 8px 24px rgba(39,47,30,.16)}
        .topbar a{color:#fff;text-decoration:none}.brand{display:inline-flex;align-items:center;gap:11px;font:italic 700 27px Georgia,serif;letter-spacing:-.8px}.brand img{display:block;width:43px;height:43px;filter:drop-shadow(0 4px 8px rgba(0,0,0,.16))}.brand-word{color:#fff}.brand-word span{color:#dfc183}.topbar nav{display:flex;align-items:center;gap:10px}.topbar nav>a{padding:11px 15px;border:1px solid transparent;border-radius:10px;color:#f3f1e8;font-size:13px;font-weight:700;transition:background .18s,border-color .18s}.topbar nav>a:hover,.topbar nav>a[aria-current="page"]{border-color:rgba(255,255,255,.2);background:rgba(255,255,255,.11)}
        .logout-form{margin:0}.topbar button{display:inline-flex;align-items:center;gap:9px;padding:10px 14px;border:1px solid rgba(255,255,255,.38);border-radius:10px;background:rgba(255,255,255,.08);color:#fff;font:600 13px Arial,sans-serif;cursor:pointer;transition:background .18s,border-color .18s}.topbar button:hover{border-color:#e1c183;background:rgba(255,255,255,.15)}.topbar button svg{width:16px;height:16px}
        main{width:min(1280px,100%);margin:auto;padding:36px 24px 70px}h1{font-size:clamp(28px,5vw,42px)}a{color:#55613a}
        @media(max-width:560px){.topbar{padding:11px 16px}.brand{font-size:22px;gap:8px}.brand img{width:37px;height:37px}.topbar nav{gap:5px}.topbar nav>a{padding:9px 8px;font-size:11px}.topbar button{padding:9px 10px;font-size:12px}.topbar button .logout-label{display:none}main{padding:25px 16px 50px}}
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('co-admin.exhibitions.index') }}" aria-label="Artfy co-admin home">
            <img src="{{ asset('artfy-logo.svg') }}" alt="">
            <span class="brand-word">Art<span>fy.</span></span>
        </a>
        <nav aria-label="Co-admin navigation">
            <a href="{{ route('co-admin.exhibitions.index') }}" aria-current="{{ request()->routeIs('co-admin.exhibitions.index') ? 'page' : 'false' }}">Exhibitions</a>
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 5H5.8A1.8 1.8 0 0 0 4 6.8v10.4A1.8 1.8 0 0 0 5.8 19H10m4-3 4-4-4-4m4 4H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><span class="logout-label">Log out</span></button>
            </form>
        </nav>
    </header>
    <main>
        @if (session('success'))
            <p role="status" style="padding:12px;background:#e5f3e5;border-radius:8px">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p role="alert" style="padding:12px;background:#fbe8e6;border-radius:8px">{{ session('error') }}</p>
        @endif
        @yield('content')
    </main>
</body>
</html>
