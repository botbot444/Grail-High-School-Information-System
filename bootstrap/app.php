<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Phase 12 — a deactivated account loses access on its next request,
        // not whenever its session happens to expire. Runs first: a
        // deactivated user should be logged out outright, not just locked
        // to the settings page for a password change.
        //
        // Locks a must-change-password account to its settings page until
        // it sets a new password. See EnsurePasswordIsChanged for details.
        $middleware->web(append: [
            \App\Http\Middleware\EnsureAccountIsActive::class,
            \App\Http\Middleware\EnsurePasswordIsChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Page expired. Please refresh and try again.',
                ], 419);
            }

            return response()->view('errors.419', ['message' => 'Page expired. Please refresh and try again.'], 419);
        });
    })->create();
