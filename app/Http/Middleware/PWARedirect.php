<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PWARedirect
{
    /**
     * Handle an incoming request.
     * 
     * Redirects users to their preferred app when accessing via PWA
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

        // Check if accessing via PWA (standalone mode)
        $isPWA = $request->query('source') === 'pwa' || 
                 $request->header('X-Requested-With') === 'PWA';

        if (!$isPWA) {
            return $next($request);
        }

        // Priority 1: Check if user has GrowNet package (auto-redirect to GrowNet)
        if (!is_null($user->lgr_package_id)) {
            return redirect('/grownet');
        }

        // Priority 2: Check user's PWA default app preference
        if ($user->pwa_default_app) {
            $appRoutes = [
                'grownet' => '/grownet',
                'growbuilder' => '/growbuilder',
                'bizboost' => '/bizboost',
                'growfinance' => '/growfinance',
                'growbiz' => '/growbiz',
                'marketplace' => '/marketplace',
                'wallet' => '/wallet',
            ];

            if (isset($appRoutes[$user->pwa_default_app])) {
                return redirect($appRoutes[$user->pwa_default_app]);
            }
        }

        // Default: Stay on dashboard
        return $next($request);
    }
}
