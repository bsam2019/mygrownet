<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // ...existing middleware...
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\RefreshCsrfToken::class, // Refresh CSRF token on every request for PWA
            // \App\Http\Middleware\DetectFraudulentActivity::class, // DISABLED - potential circular dependency
            // \App\Http\Middleware\PerformanceMonitoring::class, // DISABLED - constructor injection causing early service resolution
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'admin.or.role' => \App\Http\Middleware\AdminOrRoleMiddleware::class,
        'role.dashboard' => \App\Http\Middleware\RoleBasedDashboard::class,
        'employee' => \App\Http\Middleware\EnsureIsEmployee::class,
        'otp' => \App\Http\Middleware\RequireOtpVerification::class,
        'fraud.detection' => \App\Http\Middleware\DetectFraudulentActivity::class,
        'id.verification' => \App\Http\Middleware\RequireIdVerification::class,
        'compliance.check' => \App\Http\Middleware\ComplianceCheckMiddleware::class,
        'has_starter_kit' => \App\Http\Middleware\EnsureHasStarterKit::class,
        'premium_tier' => \App\Http\Middleware\EnsurePremiumTier::class,
    ];
}
