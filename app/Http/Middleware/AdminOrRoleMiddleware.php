<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrRoleMiddleware
{
    /**
     * Handle an incoming request.
     * Checks both is_admin field and admin role
     */
    public function handle(Request $request, Closure $next, string $role = 'admin'): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has the specified role or Administrator role
        $isAdmin = $user->hasRole($role) || $user->hasRole('Administrator') || $user->hasRole('admin');
        
        if (!$isAdmin) {
            abort(403, 'Unauthorized access. Admin privileges required.');
        }

        return $next($request);
    }
}