<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Services\CartService;
use App\Domain\GrowMart\Services\WishlistService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WishlistController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlistService,
        private readonly CartService $cartService,
    ) {}

    public function index()
    {
        $items = $this->wishlistService->getWishlist(auth()->id());
        $cartSummary = $this->cartService->getSummary(auth()->id());

        $formatted = array_map(fn($item) => [
            'id' => $item['id'],
            'product_id' => $item['product_id'],
            'name' => $item['product']['name'] ?? 'Unknown',
            'slug' => $item['product']['slug'] ?? '',
            'unit' => $item['product']['unit'] ?? '',
            'price' => $item['product']['price'] ?? 0,
            'price_formatted' => 'K' . number_format(($item['product']['price'] ?? 0) / 100, 2),
            'image' => $item['product']['images'][0]['path'] ?? null,
            'in_stock' => ($item['product']['total_stock'] ?? 0) > 0,
            'created_at' => \Carbon\Carbon::parse($item['created_at'])->diffForHumans(),
        ], $items['data'] ?? []);

        return Inertia::render('GrowMart/Wishlist/Index', [
            'items' => $formatted,
            'cartCount' => $cartSummary['item_count'],
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:growmart_products,id']);

        $result = $this->wishlistService->toggle(auth()->id(), $request->product_id);

        return back()->with($result['wishlisted'] ? 'success' : 'info', $result['message']);
    }

    public function remove(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:growmart_products,id']);

        $this->wishlistService->remove(auth()->id(), $request->product_id);

        return back()->with('info', 'Removed from wishlist.');
    }
}
