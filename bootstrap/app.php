<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AdminOrRoleMiddleware;
use App\Http\Middleware\AutoLoginToBMS;
use App\Http\Middleware\CheckAccountType;
use App\Http\Middleware\CheckBizBoostSubscription;
use App\Http\Middleware\CheckDelegatedPermission;
use App\Http\Middleware\CheckFeatureEnabled;
use App\Http\Middleware\CheckModuleAccess;
use App\Http\Middleware\CheckModuleEnabled;
use App\Http\Middleware\ComplianceCheckMiddleware;
use App\Http\Middleware\DetectFraudulentActivity;
use App\Http\Middleware\DetectSubdomain;
use App\Http\Middleware\EnsureApplicationAccess;
use App\Http\Middleware\EnsureBmsAccess;
use App\Http\Middleware\EnsureGrowStreamSubscription;
use App\Http\Middleware\EnsureHasStarterKit;
use App\Http\Middleware\EnsureIsEmployee;
use App\Http\Middleware\EnsureOrganizationAccess;
use App\Http\Middleware\EnsurePremiumTier;
use App\Http\Middleware\EnsureUserIsSeller;
use App\Http\Middleware\GeopamuAdmin;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\InjectDelegatedNavigation;
use App\Http\Middleware\InjectMarketplaceData;
use App\Http\Middleware\PreventBrowserCaching;
use App\Http\Middleware\RedirectIfNotPortalUser;
use App\Http\Middleware\RedirectToMyGrowIdentity;
use App\Http\Middleware\RefreshCsrfToken;
use App\Http\Middleware\RequireIdVerification;
use App\Http\Middleware\RequireOtpVerification;
use App\Http\Middleware\ResolveStockFlowCompany;
use App\Http\Middleware\RoleBasedDashboard;
use App\Http\Middleware\RoutingEngine;
use App\Http\Middleware\ShareModulesData;
use App\Http\Middleware\SiteUserAuth;
use App\Http\Middleware\SiteUserPermission;
use App\Http\Middleware\StockFlowAdminMiddleware;
use App\Http\Middleware\StockFlowApiAuth;
use App\Http\Middleware\StockFlowCompany;
use App\Http\Middleware\StockFlowPermission;
use App\Http\Middleware\SubdomainCheck;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

            // GrowStream subdomain routes — loaded before web.php so domain routes match first
            Route::middleware('web')
                ->group(base_path('routes/growstream.php'));

            // GrowMusic subdomain routes — loaded before web.php so domain routes match first
            Route::middleware('web')
                ->group(base_path('routes/growmusic.php'));

            // StockFlow landing subdomain (stockflow.mygrownet.com) - marketing page
            // MUST be loaded BEFORE stockflow-subdomain.php so specific domain matches first
            Route::middleware('web')
                ->group(base_path('routes/stockflow-landing.php'));

            // StockFlow company subdomain routes - loaded BEFORE web.php so
            // {account}.mygrownet.com/ matches before web.php's domain-less GET / route
            Route::middleware('web')
                ->group(base_path('routes/stockflow-subdomain.php'));

            Route::middleware('web')
                ->group(base_path('routes/stockflow-admin.php'));

            // MyGrow Identity Gateway routes — served exclusively by auth.mygrownet.com
            // These must be loaded so route names (identity.login, etc.) are available
            // for all environments. The DetectSubdomain middleware handles routing to
            // the correct subdomain. The session/validate endpoint is accessible from any domain.
            Route::middleware('web')
                ->group(base_path('routes/my-grow-identity.php'));

            // Main web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Load BOTH CMS route files with different name prefixes
            // This ensures Ziggy has all routes available regardless of environment
            Route::middleware('web')
                ->group(base_path('routes/bms-subdomain.php'));

            Route::middleware('web')
                ->group(base_path('routes/bms.php'));

            // GrowFinance subdomain routes - loaded BEFORE main growfinance.php
            Route::middleware('web')
                ->group(base_path('routes/growfinance-subdomain.php'));

            // GrowBuilder subdomain routes - NO LONGER LOADED
            // All subdomain handling (including CMS, geopamu, wowthem, and GrowBuilder sites)
            // is now done in DetectSubdomain middleware to prevent route conflicts
            // Route::middleware('web')
            //     ->group(base_path('routes/subdomain.php'));
            Route::middleware('web')
                ->group(base_path('routes/employee-portal.php'));
            Route::middleware('web')
                ->group(base_path('routes/growfinance.php'));
            Route::middleware('web')
                ->group(base_path('routes/pos.php'));
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

            // Platform API — authenticated via Sanctum
            Route::middleware('web')
                ->group(base_path('routes/platform-api.php'));
        },
    )
    // Broadcasting auth is handled by custom BroadcastAuthController
    // to support both Laravel auth and session-based investor auth
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust Cloudflare proxies - this ensures $request->getHost() returns the correct hostname
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'marketplace.data' => InjectMarketplaceData::class,
            'marketplace.seller' => EnsureUserIsSeller::class,
            'admin.or.role' => AdminOrRoleMiddleware::class,
            'role.dashboard' => RoleBasedDashboard::class,
            'otp' => RequireOtpVerification::class,
            'fraud.detection' => DetectFraudulentActivity::class,
            'id.verification' => RequireIdVerification::class,
            'compliance.check' => ComplianceCheckMiddleware::class,
            'employee' => EnsureIsEmployee::class,
            'module.access' => CheckModuleAccess::class,
            'account.type' => CheckAccountType::class,
            'bizboost.subscription' => CheckBizBoostSubscription::class,
            'delegated' => CheckDelegatedPermission::class,
            'inject.delegated.nav' => InjectDelegatedNavigation::class,
            // GrowBuilder site user middleware
            'site.auth' => SiteUserAuth::class,
            'site.permission' => SiteUserPermission::class,
            'subdomain.check' => SubdomainCheck::class,
            'geopamu.admin' => GeopamuAdmin::class,
            // BMS middleware
            'bms.access' => EnsureBmsAccess::class,
            'bms.auto-login' => AutoLoginToBMS::class,
            'module' => CheckModuleEnabled::class,
            'portal.auth' => RedirectIfNotPortalUser::class,
            'routing.engine' => RoutingEngine::class,
            'stockflow.company' => StockFlowCompany::class,
            'stockflow.admin' => StockFlowAdminMiddleware::class,
            'stockflow.permission' => StockFlowPermission::class,
            'stockflow.feature' => CheckFeatureEnabled::class,
            'stockflow.api' => StockFlowApiAuth::class,
            'has_starter_kit' => EnsureHasStarterKit::class,
            'premium_tier' => EnsurePremiumTier::class,
            'ensure.organization.access' => EnsureOrganizationAccess::class,
            'ensure.application.access' => EnsureApplicationAccess::class,
            'identity.redirect' => RedirectToMyGrowIdentity::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'growstream.subscription' => EnsureGrowStreamSubscription::class,
        ]);

        // Add Inertia and cache prevention to web middleware group
        // CRITICAL: DetectSubdomain must be FIRST to intercept custom domain requests
        $middleware->web(prepend: [
            DetectSubdomain::class,
        ]);

        $middleware->web(append: [
            RoutingEngine::class,
            HandleInertiaRequests::class,
            ShareModulesData::class,
            ResolveStockFlowCompany::class,
            PreventBrowserCaching::class,
            RefreshCsrfToken::class,
        ]);

        // CRITICAL: Laravel sorts middleware by priority. The `auth` alias resolves to
        // Authenticate, which implements the AuthenticatesRequests contract sitting at
        // priority 5 in the framework default list — so `auth` gets moved EARLY in the
        // pipeline, before any custom middleware that is not in the priority map.
        // Without this, RedirectToMyGrowIdentity (identity.redirect) runs AFTER `auth`,
        // so unauthenticated users get redirected to the subdomain /login by the auth
        // middleware before the identity.redirect middleware can send them to the
        // MyGrow Identity Gateway (auth.mygrownet.com). Prepending it before the
        // AuthenticatesRequests contract guarantees identity.redirect runs first.
        $middleware->prependToPriorityList(
            AuthenticatesRequests::class,
            RedirectToMyGrowIdentity::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 419 CSRF/session expiry — redirect to login with a message.
        // NOTE: Laravel 13's Handler::prepareException() converts TokenMismatchException
        // into a plain HttpException(419) BEFORE renderCallbacks are consulted, so we
        // must match on HttpException with status 419, not TokenMismatchException.
        $exceptions->render(function (HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return;
            }

            // Detect StockFlow subdomain
            $host = $request->getHost();
            $isStockFlowSubdomain = preg_match('/^[a-z0-9-]+\.mygrownet\.com$/i', $host)
                && ! in_array(strtolower(explode('.', $host)[0]), [
                    'bizboost', 'bizdocs', 'growbuilder', 'venture', 'grownet',
                    'growstorage', 'growstream', 'growmart', 'zamstay', 'bms', 'primeedge',
                    'stockflow', 'geopamu', 'wowthem', 'www',
                ]);

            $loginUrl = $isStockFlowSubdomain ? 'https://'.$host.'/login' : '/login';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Session expired.'], 419);
            }

            return redirect()->guest($loginUrl)->with('warning', 'Your session has expired. Please log in again.');
        });
    })->create();
