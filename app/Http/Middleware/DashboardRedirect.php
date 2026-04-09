<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardRedirect
{
    /**
     * Handle an incoming request.
     * 
     * Redirects users to their preferred dashboard based on:
     * 1. User's custom preference (pwa_default_app setting)
     * 2. User role (Admin → admin dashboard)
     * 3. GrowNet membership (lgr_package_id → grownet dashboard)
     * 4. Default (non-GrowNet members → main dashboard)
     * 
     * This applies to both PWA and browser access.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only redirect on dashboard route
        if ($request->path() !== 'dashboard') {
            return $next($request);
        }

        // Check if user is authenticated
        if (!$request->user()) {
            return $next($request);
        }

        $user = $request->user();

        // Debug logging (remove in production)
        \Log::info('DashboardRedirect Middleware', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'lgr_package_id' => $user->lgr_package_id,
            'pwa_default_app' => $user->pwa_default_app,
            'is_admin' => $user->hasRole(['Administrator', 'admin', 'superadmin']),
        ]);

        // Priority 1: Check if user has set a custom preference in settings
        if ($user->pwa_default_app) {
            $appRoutes = [
                'grownet' => '/grownet',
                'growbuilder' => '/growbuilder',
                'bizboost' => '/bizboost',
                'growfinance' => '/growfinance',
                'growbiz' => '/growbiz',
                'marketplace' => '/marketplace',
                'wallet' => '/wallet',
                'admin' => '/admin/dashboard',
                'dashboard' => '/dashboard',
            ];

            if (isset($appRoutes[$user->pwa_default_app])) {
                \Log::info('DashboardRedirect: Using custom preference', ['redirect_to' => $appRoutes[$user->pwa_default_app]]);
                return redirect($appRoutes[$user->pwa_default_app]);
            }
        }

        // Priority 2: Smart defaults based on user type (if no custom preference set)
        // Admin users -> Admin dashboard
        if ($user->hasRole(['Administrator', 'admin', 'superadmin'])) {
            \Log::info('DashboardRedirect: Admin user detected', ['redirect_to' => '/admin/dashboard']);
            return redirect('/admin/dashboard');
        }

        // GrowNet members (users with lgr_package_id) -> GrowNet dashboard
        if (!is_null($user->lgr_package_id)) {
            \Log::info('DashboardRedirect: GrowNet member detected', ['redirect_to' => '/grownet', 'lgr_package_id' => $user->lgr_package_id]);
            return redirect('/grownet');
        }

        // Priority 3: Regular users (non-GrowNet members) -> Stay on main dashboard
        \Log::info('DashboardRedirect: Regular user, staying on dashboard');
        return $next($request);
    }
}
