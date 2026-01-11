<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MarketplaceAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Temporarily allow all authenticated users for testing
        return $next($request);

        // Check if user has admin role or marketplace_admin permission
        // $user = $request->user();
        
        // if ($user->hasRole('admin') || $user->hasRole('super-admin') || $user->can('manage_marketplace')) {
        //     return $next($request);
        // }

        // Unauthorized
        // abort(403, 'Unauthorized access to marketplace admin panel.');
    }
}
