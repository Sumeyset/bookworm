<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Suspended accounts are signed out on their next page.
        $middleware->web(append: [\App\Http\Middleware\EnsureNotSuspended::class, \Illuminate\Session\Middleware\AuthenticateSession::class]);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // Behind a host's load balancer (TRUSTED_PROXIES=* or its IPs), trust its HTTPS headers.
        if ($proxies = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : explode(',', $proxies));
        }
        // Stripe signs its webhooks instead of sending a CSRF token.
        $middleware->validateCsrfTokens(except: ['stripe/webhook']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
