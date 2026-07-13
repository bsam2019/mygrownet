<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // GrowMart routes FIRST so subdomain routes match before web.php
            Route::middleware('web')
                ->group(base_path('routes/growmart.php'));

            // Load BizBoost BEFORE web routes that have domain-less root `/` route
            // so subdomain routes (Route::domain('bizboost.mygrownet.com')) match first
            Route::middleware('web')
                ->group(base_path('routes/bizboost.php'));

            // Load ZamStay subdomain routes before web.php so domain routes match first
            Route::middleware('web')
                ->group(base_path('routes/zamstay.php'));

            // Subdomain route files — loaded BEFORE web.php so Route::domain() matches first
            Route::middleware('web')
                ->group(base_path('routes/bizdocs.php'));
            Route::middleware('web')
                ->group(base_path('routes/growbuilder.php'));
            Route::middleware('web')
                ->group(base_path('routes/venture.php'));
            Route::middleware('web')
                ->group(base_path('routes/grownet.php'));
            Route::middleware('web')
                ->group(base_path('routes/growstorage.php'));
            Route::middleware('web')
                ->group(base_path('routes/primeedge.php'));

            // Main web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Load BOTH CMS route files with different name prefixes
            // This ensures Ziggy has all routes available regardless of environment
            Route::middleware('web')
                ->group(base_path('routes/cms-subdomain.php'));
            
            Route::middleware('web')
                ->group(base_path('routes/cms.php'));
            
            // GrowBuilder subdomain routes - NO LONGER LOADED
            // All subdomain handling (including CMS, geopamu, wowthem, and GrowBuilder sites)
            // is now done in DetectSubdomain middleware to prevent route conflicts
            // Route::middleware('web')
            //     ->group(base_path('routes/subdomain.php'));
            Route::middleware('web')
                ->group(base_path('routes/employee-portal.php'));
            Route::middleware('web')
                ->group(base_path('routes/growbiz.php'));
            Route::middleware('web')
                ->group(base_path('routes/growfinance.php'));
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
                ->group(base_path('routes/quick-invoice.php'));
            Route::middleware('web')
                ->group(base_path('routes/ubumi.php'));
            Route::middleware('web')
                ->group(base_path('routes/portal.php'));
        },
    )
    // Broadcasting auth is handled by custom BroadcastAuthController
    // to support both Laravel auth and session-based investor auth
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust Cloudflare proxies - this ensures $request->getHost() returns the correct hostname
        $middleware->trustProxies(at: '*', headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO);
        
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'marketplace.data' => \App\Http\Middleware\InjectMarketplaceData::class,
            'marketplace.seller' => \App\Http\Middleware\EnsureUserIsSeller::class,
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
            'geopamu.admin' => \App\Http\Middleware\GeopamuAdmin::class,
            // CMS middleware
            'cms.access' => \App\Http\Middleware\EnsureCmsAccess::class,
            'cms.auto-login' => \App\Http\Middleware\AutoLoginToCMS::class,
            'module' => \App\Http\Middleware\CheckModuleEnabled::class,
            'portal.auth' => \App\Http\Middleware\RedirectIfNotPortalUser::class,
        ]);

        // Add Inertia and cache prevention to web middleware group
        // CRITICAL: DetectSubdomain must be FIRST to intercept custom domain requests
        $middleware->web(prepend: [
            \App\Http\Middleware\DetectSubdomain::class,
        ]);
        
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\ShareModulesData::class,
            \App\Http\Middleware\ResolveStockFlowCompany::class,
            \App\Http\Middleware\PreventBrowserCaching::class,
            \App\Http\Middleware\RefreshCsrfToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle GrowBiz domain exceptions
        $exceptions->render(function (\App\Domain\GrowBiz\Exceptions\GrowBizException $e, $request) {
            return \App\Domain\GrowBiz\Exceptions\Handler::render($e, $request);
        });

        // Handle 419 CSRF/session expiry — redirect to login with a message
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Session expired.'], 419);
            }

            if ($request->header('X-Inertia')) {
                return redirect()->guest('/login')->with('warning', 'Your session has expired. Please log in again.');
            }

            return redirect()->guest('/login')->with('warning', 'Your session has expired. Please log in again.');
        });
    })->create();
