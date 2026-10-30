<?php

use App\Http\Middleware\EnsureArtistMembershipActive;
use App\Http\Middleware\EnsureUserRole;
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

        /*
         * Trust the proxy used by the ngrok HTTPS preview.
         */
        $middleware->trustProxies(at: '*');

        /*
         * Register custom middleware aliases.
         *
         * artist.active:
         * Allows only artists whose membership is approved,
         * payment is verified, and membership is active.
         *
         * role:
         * Restricts routes to the specified user role,
         * for example role:admin.
         */
        $middleware->alias([
            'artist.active' => EnsureArtistMembershipActive::class,
            'role' => EnsureUserRole::class,
        ]);

        /*
         * Exclude payment callback endpoints from CSRF checks.
         *
         * These controllers MUST independently validate payment
         * responses with SSLCOMMERZ before updating payment,
         * order, or membership statuses.
         */
        $middleware->validateCsrfTokens(except: [
            'payment/sslcommerz/*',
            'membership/payment/*',
            'exhibition/payment/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
