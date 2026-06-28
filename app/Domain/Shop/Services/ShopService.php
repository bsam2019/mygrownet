<?php

namespace App\Domain\Shop\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\PointService;
use Illuminate\Support\Facades\DB;

class ShopService
{
    public function __construct(
        private PointService $pointService
    ) {}

    public function createOrder(User $user, array $cartItems): Order
    {
        $subtotal = 0;
        $totalBP = 0;
        $orderItems = [];

        // Calculate totals
        foreach ($cartItems as $productId => $quantity) {
            $product = Product::find($productId);
            if (!$product || !$product->is_active) {
                continue;
            }

            $itemTotal = $product->price * $quantity;
            $itemBP = $product->calculateBP($itemTotal);

            $orderItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'price' => $product->price,
                'bp_earned' => $itemBP,
            ];

            $subtotal += $itemTotal;
            $totalBP += $itemBP;
        }

        // Check wallet balance
        if ($user->wallet_balance < $subtotal) {
            throw new \Exception('Insufficient wallet balance');
        }

        return DB::transaction(function () use ($user, $subtotal, $totalBP, $orderItems) {
            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'total_bp_earned' => $totalBP,
                'status' => 'pending',
                'payment_method' => 'wallet',
                'payment_status' => 'pending',
            ]);

            // Create order items and update stock
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'bp_earned' => $item['bp_earned'],
                ]);

                $item['product']->decrement('stock_quantity', $item['quantity']);
            }

            // Deduct from wallet
            $user->decrement('wallet_balance', $subtotal);

            // Mark order as paid
            $order->markAsPaid();

            // Award BP points
            $this->pointService->awardPoints(
                $user,
                'shop_purchase',
                0,
                $totalBP,
                "Shop purchase - Order #{$order->order_number}"
            );

            return $order;
        });
    }

    public function calculateBPForAmount(float $amount, int $bpValue): int
    {
        return (int) (($amount / 100) * $bpValue);
    }
}
