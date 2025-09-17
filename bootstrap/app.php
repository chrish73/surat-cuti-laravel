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
        // Daftarkan middleware alias di sini
        $middleware->alias([
            'is.admin' => \App\Http\Middleware\IsAdmin::class,
        ]);

        // Contoh untuk middleware group jika diperlukan
        // $middleware->web(append: [
        //     //
        // ]);

        // $middleware->api(prepend: [
        //     //
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
