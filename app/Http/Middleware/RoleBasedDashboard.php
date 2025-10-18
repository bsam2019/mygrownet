<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedDashboard
{
    /**
     * Handle an incoming request and redirect to appropriate dashboard
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return $next($request);
        }

        // If user is accessing the generic dashboard route, redirect to role-specific dashboard
        if ($request->routeIs('dashboard')) {
            if ($user->hasRole('Administrator') || $user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            
            if ($this->isManager($user)) {
                return redirect()->route('manager.dashboard');
            }
            
            // Default to investor dashboard
            return redirect()->route('investor.dashboard');
        }

        return $next($request);
    }

    /**
     * Simple manager check
     */
    private function isManager($user): bool
    {
        return in_array($user->rank, ['manager', 'regional_manager']) || 
               $user->email === 'manager@mygrownet.com' ||
               $user->hasRole('Investment Manager') ||
               $user->hasRole('manager');
    }
}