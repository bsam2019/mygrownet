<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Domain\Marketplace\Services\CartService;
use App\Domain\Marketplace\Services\OrderService;
use App\Domain\Marketplace\Services\SellerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private SellerService $sellerService,
    ) {}

    public function index(Request $request)
    {
        $cart = $this->cartService->getCartSummary();

        if (empty($cart['items'])) {
            return redirect()->route('marketplace.cart')
                ->withErrors(['cart' => 'Your cart is empty.']);
        }

        if ($cart['is_multi_seller']) {
            return redirect()->route('marketplace.cart')
                ->withErrors(['cart' => 'Please checkout items from one seller at a time.']);
        }

        return Inertia::render('Marketplace/Checkout', [
            'cart' => $cart,
            'provinces' => $this->sellerService->getProvinces(),
            'deliveryMethods' => [
                ['value' => 'self', 'label' => 'Seller Delivery', 'fee' => 2500],
                ['value' => 'pickup', 'label' => 'Pickup (Free)', 'fee' => 0],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'delivery_method' => 'required|in:self,pickup,courier',
            'province' => 'required_if:delivery_method,self,courier|string',
            'district' => 'required_if:delivery_method,self,courier|string',
            'address' => 'required_if:delivery_method,self,courier|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        $cartItems = $this->cartService->getCartForCheckout();

        if (empty($cartItems)) {
            return back()->withErrors(['cart' => 'Your cart is empty or items are no longer available.']);
        }

        try {
            $order = $this->orderService->createOrder(
                $request->user()->id,
                $cartItems,
                [
                    'method' => $validated['delivery_method'],
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'province' => $validated['province'] ?? null,
                    'district' => $validated['district'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            // Clear cart after successful order
            $this->cartService->clearCart();

            return redirect()->route('marketplace.orders.payment', $order->id)
                ->with('success', 'Order created! Please complete payment.');

        } catch (\Exception $e) {
            return back()->withErrors(['checkout' => $e->getMessage()]);
        }
    }

    public function payment(int $orderId)
    {
        $order = $this->orderService->getById($orderId);

        if (!$order || $order->buyer_id !== auth()->id()) {
            abort(404);
        }

        if ($order->status !== 'pending') {
            return redirect()->route('marketplace.orders.show', $order->id);
        }

        return Inertia::render('Marketplace/Payment', [
            'order' => $order,
        ]);
    }

    public function confirmPayment(Request $request, int $orderId)
    {
        $request->validate([
            'payment_reference' => 'required|string|max:100',
        ]);

        $order = $this->orderService->getById($orderId);

        if (!$order || $order->buyer_id !== auth()->id()) {
            abort(404);
        }

        if ($order->status !== 'pending') {
            return redirect()->route('marketplace.orders.show', $order->id);
        }

        try {
            // In production, verify payment with payment gateway
            $this->orderService->markAsPaid($orderId, $request->payment_reference);

            return redirect()->route('marketplace.orders.show', $order->id)
                ->with('success', 'Payment confirmed! The seller has been notified.');

        } catch (\Exception $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
    }
}
