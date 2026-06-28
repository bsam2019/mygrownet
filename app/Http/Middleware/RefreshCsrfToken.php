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
        // IMPORTANT: Do NOT regenerate CSRF token on every request!
        // This causes 419 errors because the token in forms becomes stale.
        // Laravel already handles CSRF token lifecycle properly.
        // Only regenerate on login (handled by session()->regenerate())

        $response = $next($request);

        // Add CSRF token to response headers for PWA and AJAX requests
        // This helps frontend apps get the current token
        if (
            !$response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse &&
            !$response instanceof \Symfony\Component\HttpFoundation\StreamedResponse &&
            method_exists($response, 'header')
        ) {
            $response->header('X-CSRF-Token', csrf_token());
        }

        return $response;
    }
}
