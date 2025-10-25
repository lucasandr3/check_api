<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenancy' => \App\Http\Middleware\TenancyMiddleware::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'any.permission' => \App\Http\Middleware\CheckAnyPermission::class,
        ]);
        
        $middleware->web([
            \App\Http\Middleware\TenancyMiddleware::class,
        ]);
        
        $middleware->api([
            \App\Http\Middleware\TenancyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
