<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Domain\Marketplace\Services\CartService;

class InjectMarketplaceData
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $cart = $this->cartService->getCartSummary();
        
        Inertia::share([
            'cartCount' => $cart['item_count'],
        ]);

        return $next($request);
    }
}
