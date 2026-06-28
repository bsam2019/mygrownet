<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotPortalUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('portal.login');
        }

        if ($request->user()->portalCustomers()->count() === 0) {
            return redirect()->route('portal.login')
                ->with('warning', 'No customer portal access.');
        }

        return $next($request);
    }
}
