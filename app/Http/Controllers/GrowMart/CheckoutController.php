<?php

namespace App\Http\Controllers\GrowMart;

use App\Http\Controllers\Controller;
use App\Domain\GrowMart\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    public function index()
    {
        $cartSummary = $this->cartService->getSummary(auth()->id());

        if ($cartSummary['item_count'] === 0) {
            return redirect()->route('growmart.cart')
                ->with('error', 'Your cart is empty.');
        }

        return Inertia::render('GrowMart/Checkout', [
            'cart' => $cartSummary,
            'cartCount' => $cartSummary['item_count'],
            'deliveryMethods' => [
                ['id' => 'own_vehicle', 'name' => 'Own Vehicle', 'description' => 'Collect from our pickup point', 'fee' => 0],
                ['id' => 'yango', 'name' => 'Yango Delivery', 'description' => 'Delivered to your door via Yango', 'fee' => config('growmart.delivery_fees.yango', 3000)],
                ['id' => 'pickup', 'name' => 'Store Pickup', 'description' => 'Free pickup at our location', 'fee' => 0],
            ],
            'paymentMethods' => [
                ['id' => 'mobile_money', 'name' => 'Mobile Money', 'description' => 'Airtel Money / MTN', 'icon' => 'mobile', 'coming_soon' => false],
                ['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'description' => 'Direct bank deposit', 'icon' => 'bank', 'coming_soon' => false],
                ['id' => 'crypto', 'name' => 'Cryptocurrency', 'description' => 'Pay with crypto from abroad', 'icon' => 'crypto', 'coming_soon' => false],
                ['id' => 'card', 'name' => 'Card Payment', 'description' => 'Visa / Mastercard', 'icon' => 'card', 'coming_soon' => true],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'delivery_method' => 'required|in:own_vehicle,yango,pickup',
            'payment_method' => 'required|in:mobile_money,bank_transfer,crypto',
            'delivery_zone' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:20',
            'special_instructions' => 'nullable|string|max:1000',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        session(['growmart.checkout' => $validated]);

        return redirect()->route('growmart.payment.show');
    }

    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'required|integer|min:0',
        ]);

        $couponService = app(\App\Domain\GrowMart\Services\CouponService::class);
        $coupon = $couponService->findByCode($request->code);
        $result = $couponService->validateCoupon($coupon, $request->subtotal);

        return response()->json($result);
    }
}
