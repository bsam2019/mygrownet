<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiteUserPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->attributes->get('site_user');

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        if (!$user->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this resource');
        }

        return $next($request);
    }
}
