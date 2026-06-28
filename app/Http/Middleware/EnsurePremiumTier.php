<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePremiumTier
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->has_starter_kit) {
            return redirect()
                ->route('mygrownet.starter-kit.purchase')
                ->with('error', 'You need to purchase a starter kit first.');
        }

        if ($user->starter_kit_tier !== 'premium') {
            return redirect()
                ->route('mygrownet.starter-kit.upgrade')
                ->with('info', 'This feature is available for Premium tier members. Upgrade now!');
        }

        return $next($request);
    }
}
