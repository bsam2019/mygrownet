<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GeopamuAdmin
{
    /**
     * Handle an incoming request.
     * 
     * Only allows users with 'manage-geopamu' permission (NOT admin role)
     * This ensures Geopamu admins don't get access to MyGrowNet admin
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Please login to access the Geopamu admin dashboard.');
        }

        $user = auth()->user();
        
        // ONLY check for manage-geopamu permission
        // Do NOT allow admin role to prevent cross-access
        if ($user->can('manage-geopamu')) {
            return $next($request);
        }

        // Unauthorized access
        abort(403, 'You do not have permission to access the Geopamu admin dashboard.');
    }
}
