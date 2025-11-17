<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasStarterKit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->has_starter_kit) {
            return redirect()
                ->route('mygrownet.starter-kit.purchase')
                ->with('error', 'You need to purchase a starter kit to access this content.');
        }

        return $next($request);
    }
}
