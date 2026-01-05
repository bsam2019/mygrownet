<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/employee-portal.php'));
            Route::middleware('web')
                ->group(base_path('routes/growbiz.php'));
            Route::middleware('web')
                ->group(base_path('routes/growfinance.php'));
            Route::middleware('web')
                ->group(base_path('routes/bizboost.php'));
            Route::middleware('web')
                ->group(base_path('routes/pos.php'));
            Route::middleware('web')
                ->group(base_path('routes/inventory.php'));
            Route::middleware('web')
                ->group(base_path('routes/marketplace.php'));
            Route::middleware('web')
                ->group(base_path('routes/admin-marketplace.php'));
            Route::middleware('web')
                ->group(base_path('routes/lifeplus.php'));
            Route::middleware('web')
                ->group(base_path('routes/growbuilder.php'));
            Route::middleware('web')
                ->group(base_path('routes/quick-invoice.php'));
            // GrowBuilder subdomain routes
            Route::middleware('web')
                ->group(base_path('routes/subdomain.php'));
        },
    )
    // Broadcasting auth is handled by custom BroadcastAuthController
    // to support both Laravel auth and session-based investor auth
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'marketplace.data' => \App\Http\Middleware\InjectMarketplaceData::class,
            'admin.or.role' => \App\Http\Middleware\AdminOrRoleMiddleware::class,
            'role.dashboard' => \App\Http\Middleware\RoleBasedDashboard::class,
            'otp' => \App\Http\Middleware\RequireOtpVerification::class,
            'fraud.detection' => \App\Http\Middleware\DetectFraudulentActivity::class,
            'id.verification' => \App\Http\Middleware\RequireIdVerification::class,
            'compliance.check' => \App\Http\Middleware\ComplianceCheckMiddleware::class,
            'employee' => \App\Http\Middleware\EnsureIsEmployee::class,
            'module.access' => \App\Http\Middleware\CheckModuleAccess::class,
            'account.type' => \App\Http\Middleware\CheckAccountType::class,
            'bizboost.subscription' => \App\Http\Middleware\CheckBizBoostSubscription::class,
            'delegated' => \App\Http\Middleware\CheckDelegatedPermission::class,
            'inject.delegated.nav' => \App\Http\Middleware\InjectDelegatedNavigation::class,
            // GrowBuilder site user middleware
            'site.auth' => \App\Http\Middleware\SiteUserAuth::class,
            'site.permission' => \App\Http\Middleware\SiteUserPermission::class,
            'subdomain.check' => \App\Http\Middleware\SubdomainCheck::class,
        ]);

        // Add Inertia and cache prevention to web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\PreventBrowserCaching::class,
            \App\Http\Middleware\RefreshCsrfToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle GrowBiz domain exceptions
        $exceptions->render(function (\App\Domain\GrowBiz\Exceptions\GrowBizException $e, $request) {
            return \App\Domain\GrowBiz\Exceptions\Handler::render($e, $request);
        });
    })->create();
