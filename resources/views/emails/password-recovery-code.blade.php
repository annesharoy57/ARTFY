<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Artfy recovery code</title></head>
<body style="margin:0;padding:32px 16px;background:#f5f3e8;color:#414c2c;font-family:Arial,sans-serif">
    <main style="max-width:520px;margin:0 auto;padding:36px;border:1px solid #e2e1d5;border-radius:18px;background:#fffefa">
        <p style="margin:0 0 24px;color:#596b43;font-family:Georgia,serif;font-size:30px;font-weight:bold">Artfy.</p>
        <h1 style="margin:0 0 12px;color:#39452f;font-family:Georgia,serif;font-size:28px;font-weight:normal">Password recovery</h1>
        <p style="color:#686f59;line-height:1.7">Hello {{ $userName }}, use this code to reset your Artfy password:</p>
        <p style="margin:24px 0;padding:18px;border-radius:12px;background:#f0f1e5;color:#414c2c;font-size:32px;font-weight:bold;letter-spacing:9px;text-align:center">{{ $code }}</p>
        <p style="color:#686f59;line-height:1.7">This code expires in {{ $expiresInMinutes }} minutes. If you didn’t request a password reset, you can ignore this email. Your password will not change unless the code is verified.</p>
    </main>
</body>
</html>
