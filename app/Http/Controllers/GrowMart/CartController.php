<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Services\CartService;
use App\Models\GrowMart\GrowMartProduct;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    public function index()
    {
        $cartSummary = $this->cartService->getSummary(auth()->id());

        return Inertia::render('GrowMart/Cart', [
            'cart' => $cartSummary,
            'cartCount' => $cartSummary['item_count'],
        ]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:growmart_products,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $product = GrowMartProduct::findOrFail($validated['product_id']);
        if ($product->status !== 'active') {
            return back()->with('error', 'This product is not available.');
        }

        $cartSummary = $this->cartService->addItem(
            auth()->id(),
            $validated['product_id'],
            $validated['quantity']
        );

        if ($request->wantsJson()) {
            return response()->json($cartSummary);
        }

        return back()->with('success', 'Added to cart.');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:growmart_products,id',
            'quantity' => 'required|integer|min:0|max:99',
        ]);

        $this->cartService->updateQuantity(
            auth()->id(),
            $validated['product_id'],
            $validated['quantity']
        );

        return back();
    }

    public function remove(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:growmart_products,id',
        ]);

        $this->cartService->removeItem(auth()->id(), $validated['product_id']);

        return back();
    }

    public function clear()
    {
        $this->cartService->clearCart(auth()->id());
        return back();
    }

    public function summary()
    {
        return response()->json(
            $this->cartService->getSummary(auth()->id())
        );
    }
}
