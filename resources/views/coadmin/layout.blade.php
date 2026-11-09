<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Exhibition Co-admin | Artfy')</title>
    <style>
        :root{font-family:Arial,sans-serif;color:#292d22;background:#f8f6f2}
        *{box-sizing:border-box}body{margin:0}.topbar{display:flex;justify-content:space-between;align-items:center;padding:18px max(24px,calc((100% - 1120px)/2));background:#414c2c;color:#fff}
        .topbar a{color:#fff;text-decoration:none}.brand{font:700 23px Georgia,serif;letter-spacing:2px}.topbar nav{display:flex;align-items:center;gap:18px}
        .topbar button{padding:8px 12px;border:1px solid #fff;border-radius:6px;background:transparent;color:#fff;font:inherit;cursor:pointer}
        main{width:min(1120px,100%);margin:auto;padding:36px 24px 70px}h1{font-size:clamp(28px,5vw,42px)}a{color:#55613a}
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('co-admin.exhibitions.index') }}">ARTFY</a>
        <nav aria-label="Co-admin navigation">
            <a href="{{ route('co-admin.exhibitions.index') }}">Online Exhibitions</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Log out</button>
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
