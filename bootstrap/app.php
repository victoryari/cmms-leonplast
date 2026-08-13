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
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'api-permission' => \App\Http\Middleware\ApiPermissionMiddleware::class,
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Interceptar ANTES de que Laravel intente usar vistas de error (RegisterErrorViewPaths)
        // IMPORTANTE: usar new \Illuminate\Http\Response() en lugar de response() helper
        // porque el helper requiere el contenedor que aún puede no estar inicializado
        $exceptions->render(function (\Throwable $e, $request) {
            $body =
                "EXCEPCION ORIGINAL EN LARAVEL:\n" .
                "Tipo: " . get_class($e) . "\n" .
                "Mensaje: " . $e->getMessage() . "\n" .
                "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n\n" .
                "Trace:\n" . $e->getTraceAsString();

            return new \Illuminate\Http\Response($body, 500, ['Content-Type' => 'text/plain']);
        });
    })->create();

