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
     * This middleware is NO LONGER USED for automatic redirects.
     * Redirects are now handled ONLY at login in AuthenticatedSessionController.
     * 
     * This middleware now only allows access to /dashboard for all authenticated users.
     * Users can navigate freely between dashboards without automatic redirects.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Simply allow access to dashboard for all authenticated users
        // No automatic redirects - users can navigate freely
        return $next($request);
    }
}
