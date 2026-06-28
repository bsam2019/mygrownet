<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        $user = auth()->user();
        
        // Check if user has admin role
        if (!$user->hasRole('Administrator') && !$user->hasRole('admin')) {
            abort(403, 'Administrator access required.');
        }

        return $next($request);
    }
}
