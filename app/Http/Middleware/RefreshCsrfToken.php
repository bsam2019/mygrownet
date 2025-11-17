<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Regenerate CSRF token on every request to prevent 419 errors
        // This is especially important for PWA where sessions may persist longer
        if ($request->user()) {
            $request->session()->regenerateToken();
        }

        $response = $next($request);

        // Add CSRF token to response headers for PWA and AJAX requests
        $response->header('X-CSRF-Token', csrf_token());
        
        // Also add to meta tag via header for Vue to pick up
        $response->header('X-CSRF-Token-Meta', csrf_token());

        return $response;
    }
}
