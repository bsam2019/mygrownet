<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Domain\Marketplace\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function index()
    {
        $cart = $this->cartService->getCartSummary();

        return Inertia::render('Marketplace/Cart', [
            'cart' => $cart,
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:marketplace_products,id',
            'quantity' => 'integer|min:1|max:100',
        ]);

        try {
            $cart = $this->cartService->addItem(
                $request->product_id,
                $request->quantity ?? 1
            );

            return back()->with('success', 'Item added to cart.');
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:marketplace_products,id',
            'quantity' => 'required|integer|min:0|max:100',
        ]);

        try {
            $cart = $this->cartService->updateQuantity(
                $request->product_id,
                $request->quantity
            );

            return back()->with('success', 'Cart updated.');
        } catch (\Exception $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }
    }

    public function remove(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:marketplace_products,id',
        ]);

        $cart = $this->cartService->removeItem($request->product_id);

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        $this->cartService->clearCart();

        return back()->with('success', 'Cart cleared.');
    }

    public function summary()
    {
        return response()->json($this->cartService->getCartSummary());
    }
}
