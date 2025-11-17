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
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'admin.or.role' => \App\Http\Middleware\AdminOrRoleMiddleware::class,
            'role.dashboard' => \App\Http\Middleware\RoleBasedDashboard::class,
            'otp' => \App\Http\Middleware\RequireOtpVerification::class,
            'fraud.detection' => \App\Http\Middleware\DetectFraudulentActivity::class,
            'id.verification' => \App\Http\Middleware\RequireIdVerification::class,
            'compliance.check' => \App\Http\Middleware\ComplianceCheckMiddleware::class,
        ]);

        // Add Inertia and cache prevention to web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\PreventBrowserCaching::class,
            \App\Http\Middleware\RefreshCsrfToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
