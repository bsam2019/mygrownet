<?php

namespace App\Http\Controllers;

use App\Domain\Shop\Services\ShopService;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function __construct(
        private ShopService $shopService
    ) {}

    public function index()
    {
        $cart = session()->get('cart', []);
        $cartItems = [];
        $subtotal = 0;
        $totalBP = 0;

        foreach ($cart as $productId => $quantity) {
            $product = Product::find($productId);
            if ($product && $product->is_active) {
                $itemTotal = $product->price * $quantity;
                $itemBP = $product->calculateBP($itemTotal);
                
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $itemTotal,
                    'bp' => $itemBP,
                ];
                
                $subtotal += $itemTotal;
                $totalBP += $itemBP;
            }
        }

        return Inertia::render('Shop/Cart', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'totalBP' => $totalBP,
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active || !$product->isInStock()) {
            return back()->with('error', 'Product is not available');
        }

        $cart = session()->get('cart', []);
        $currentQuantity = $cart[$product->id] ?? 0;
        $newQuantity = $currentQuantity + $request->quantity;

        if ($newQuantity > $product->stock_quantity) {
            return back()->with('error', 'Not enough stock available');
        }

        $cart[$product->id] = $newQuantity;
        session()->put('cart', $cart);

        return back()->with('success', 'Product added to cart');
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $cart = session()->get('cart', []);

        if ($request->quantity == 0) {
            unset($cart[$request->product_id]);
        } else {
            $cart[$request->product_id] = $request->quantity;
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Cart updated');
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        unset($cart[$request->product_id]);
        session()->put('cart', $cart);

        return back()->with('success', 'Product removed from cart');
    }

    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Your cart is empty');
        }

        try {
            $order = $this->shopService->createOrder($request->user(), $cart);
            
            // Clear cart
            session()->forget('cart');

            return redirect()->route('shop.orders.show', $order)
                ->with('success', "Order placed successfully! You earned {$order->total_bp_earned} BP");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function orders()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return Inertia::render('Shop/Orders', [
            'orders' => $orders,
        ]);
    }

    public function showOrder(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product');

        return Inertia::render('Shop/OrderDetail', [
            'order' => $order,
        ]);
    }
}
