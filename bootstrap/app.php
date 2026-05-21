<?php

use App\Http\Middleware\EnsureCanWrite;
use App\Http\Middleware\EnsureTwoFactorAuthenticated;
use App\Http\Middleware\ResolveCustomDomain;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            ResolveCustomDomain::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->alias([
            'can.write' => EnsureCanWrite::class,
            '2fa' => EnsureTwoFactorAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
    })->create();
