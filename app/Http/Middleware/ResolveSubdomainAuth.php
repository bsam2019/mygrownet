<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class ResolveSubdomainAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if (preg_match('/^(?:www\.)?([a-z0-9-]+)\.mygrownet\.com$/i', $host, $matches)) {
            $subdomain = strtolower($matches[1]);

            $guardMap = [
                'stockflow' => 'stockflow',
                'primeedge' => 'primeedge',
            ];

            if (isset($guardMap[$subdomain])) {
                $guard = $guardMap[$subdomain];
                if (!Auth::guard('web')->check() && Auth::guard($guard)->check()) {
                    Config::set('auth.defaults.guard', $guard);
                    $request->attributes->set('_auth_guard', $guard);
                }
            }
        }

        return $next($request);
    }
}
