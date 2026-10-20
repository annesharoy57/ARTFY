<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Trust the proxy used by the ngrok HTTPS preview.
        $middleware->trustProxies(at: '*');

        // Allow SSLCOMMERZ to send payment callbacks without
        // a Laravel CSRF token. These endpoints must validate
        // payment details with SSLCOMMERZ before updating orders.
        $middleware->validateCsrfTokens(except: [
            'payment/sslcommerz/*',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();