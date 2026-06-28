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
            'cartItems' => collect($cart['items'] ?? [])->take(3)->map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'formatted_price' => 'K' . number_format($item['price'] / 100, 2),
                    'total' => $item['total'],
                    'formatted_total' => 'K' . number_format($item['total'] / 100, 2),
                    'image_url' => $item['image_url'] ?? null,
                ];
            })->values(),
            'cartSubtotal' => $cart['subtotal'],
            'cartFormattedSubtotal' => 'K' . number_format($cart['subtotal'] / 100, 2),
        ]);

        return $next($request);
    }
}
