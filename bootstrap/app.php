<?php

<<<<<<< HEAD
=======
use App\Http\Middleware\AdminCheck;
use App\Http\Middleware\Authenticate;
>>>>>>> origin/feat/p1-auth-admin
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
<<<<<<< HEAD
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'lists/*',
            'dashboard',
            'notifications',
=======
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.jwt' => Authenticate::class,
            'admin' => AdminCheck::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            '*',
>>>>>>> origin/feat/p1-auth-admin
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
<<<<<<< HEAD
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
=======
            fn (Request $request) => true,
>>>>>>> origin/feat/p1-auth-admin
        );
    })->create();
