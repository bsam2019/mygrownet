<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\MarketplaceSeller;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSeller
{
    /**
     * Handle an incoming request.
     * Ensures the authenticated user is a registered marketplace seller.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $seller = MarketplaceSeller::where('user_id', $request->user()->id)->first();

        if (!$seller) {
            // User is not a seller, redirect to registration
            return redirect()->route('marketplace.seller.register')
                ->with('info', 'Please register as a seller to access the seller dashboard.');
        }

        // Share seller with all views
        $request->attributes->set('seller', $seller);

        return $next($request);
    }
}
