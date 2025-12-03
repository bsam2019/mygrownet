<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class GrowFinanceStandalone
{
    /**
     * Handle an incoming request.
     * 
     * Sets the root view to the GrowFinance standalone blade template
     * for PWA standalone mode support.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use the GrowFinance standalone blade template
        Inertia::setRootView('growfinance');

        return $next($request);
    }
}
