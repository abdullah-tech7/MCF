<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    basePath: dirname(__DIR__),
)
    ->withRouting(
        web: __DIR__ . '/../app/MCF/mcf_routes.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(
        function (Middleware $middleware): void {
            $middleware->web([
                'setlocale' => \App\MCF\Middleware\SetLocaleMiddleware::class,
                'session.security' => \App\MCF\Middleware\McfSessionSecurityMiddleware::class,
                'access' => \App\MCF\Middleware\McfAccessMiddleware::class,
            ]);
        },
    )
    ->withExceptions(
        function (Exceptions $exceptions): void {
            //
        },
    )
    ->create();