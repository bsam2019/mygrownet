<?php

namespace App\Domain\GrowMart\Services;

use App\Models\GrowMart\GrowMartCoupon;
use App\Models\GrowMart\GrowMartInventory;
use App\Models\GrowMart\GrowMartOrder;
use App\Models\GrowMart\GrowMartProduct;
use App\Notifications\GrowMartOrderNotification;
use App\Notifications\GrowMart\LowStockAlertNotification;
use App\Domain\GrowMart\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponService $couponService,
        private readonly NotificationService $notificationService,
    ) {}

    public function createOrder(int $userId, array $data): GrowMartOrder
    {
        $summary = $this->cartService->getSummary($userId);

        if ($summary['item_count'] === 0) {
            throw new \RuntimeException('Cart is empty.');
        }

        $insufficient = [];
        foreach ($summary['items'] as $item) {
            $product = GrowMartProduct::withSum('inventory', 'quantity')->find($item['product_id']);
            $available = $product ? (int) $product->inventory_sum_quantity : 0;
            if ($item['quantity'] > $available) {
                $insufficient[] = "{$item['name']} (requested {$item['quantity']}, available {$available})";
            }
        }

        if (!empty($insufficient)) {
            throw new \RuntimeException('Insufficient stock for: ' . implode('; ', $insufficient));
        }

        return DB::transaction(function () use ($userId, $data, $summary) {
            $deliveryFee = config('growmart.delivery_fees.' . ($data['delivery_method'] ?? ''), 0);
            $discount = 0;
            $couponId = null;

            if (!empty($data['coupon_code'])) {
                $coupon = $this->couponService->findByCode($data['coupon_code']);
                if ($coupon) {
                    $result = $this->couponService->validateCoupon($coupon, $summary['subtotal']);
                    if ($result['valid']) {
                        $discount = $result['discount'];
                        $couponId = $coupon->id;
                        $this->couponService->incrementUsage($coupon);
                    }
                }
            }

            $total = $summary['subtotal'] + $deliveryFee - $discount;

            $order = GrowMartOrder::create([
                'order_number' => 'GM-' . strtoupper(Str::random(8)) . '-' . time(),
                'user_id' => $userId,
                'coupon_id' => $couponId,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'manual',
                'delivery_method' => $data['delivery_method'],
                'delivery_zone' => $data['delivery_zone'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'special_instructions' => $data['special_instructions'] ?? null,
                'subtotal' => $summary['subtotal'],
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'total' => max($total, 0),
            ]);

            foreach ($summary['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['total'],
                ]);

                $product = GrowMartProduct::find($item['product_id']);
                $remaining = $item['quantity'];
                $inventoryRows = $product->inventory()
                    ->orderByDesc('quantity')
                    ->lockForUpdate()
                    ->get();
                foreach ($inventoryRows as $inv) {
                    if ($remaining <= 0) break;
                    $deduct = min($inv->quantity, $remaining);
                    $inv->decrement('quantity', $deduct);
                    $remaining -= $deduct;
                }
            }

            $this->checkLowStockAfterDeduction($summary['items']);

            $this->cartService->clearCart($userId);

            $order->load('user');
            $this->notificationService->notify(
                $order->user,
                'growmart.order_placed',
                'Order Placed',
                "Order {$order->order_number} placed successfully!",
                route('growmart.orders.show', $order->id),
                'View Order',
                'orders',
                'normal',
                ['order_number' => $order->order_number, 'order_id' => $order->id, 'total' => $order->total],
            );
            $order->user->notify(new GrowMartOrderNotification('order_placed', [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'total' => $order->total,
                'items' => $summary['items'],
            ]));

            $this->notifyAdmins($order);

            return $order;
        });
    }

    public function getOrdersForUser(int $userId, int $perPage = 20)
    {
        return GrowMartOrder::where('user_id', $userId)
            ->with('items')
            ->latest()
            ->paginate($perPage);
    }

    public function getOrder(int $orderId, ?int $userId = null): GrowMartOrder
    {
        $query = GrowMartOrder::with('items', 'coupon');
        if ($userId) {
            $query->where('user_id', $userId);
        }
        return $query->findOrFail($orderId);
    }

    public function cancelOrder(int $orderId, int $userId): GrowMartOrder
    {
        $order = $this->getOrder($orderId, $userId);

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            throw new \RuntimeException('Order cannot be cancelled in its current state.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            foreach ($order->items as $item) {
                if (!$item->product_id) continue;
                $remaining = $item->quantity;
                $inventoryRows = GrowMartInventory::where('product_id', $item->product_id)
                    ->orderBy('quantity')
                    ->lockForUpdate()
                    ->get();
                foreach ($inventoryRows as $inv) {
                    if ($remaining <= 0) break;
                    $inv->increment('quantity', $remaining);
                    $remaining = 0;
                }
            }
        });

        $order->load('user');
        $this->notificationService->notify(
            $order->user,
            'growmart.order_cancelled',
            'Order Cancelled',
            "Order {$order->order_number} has been cancelled.",
            route('growmart.orders.show', $order->id),
            'View Order',
            'orders',
            'high',
            ['order_number' => $order->order_number, 'order_id' => $order->id],
        );
        $order->user->notify(new GrowMartOrderNotification('order_cancelled', [
            'order_number' => $order->order_number,
            'order_id' => $order->id,
        ]));

        return $order;
    }

    public function updateStatus(int $orderId, string $status): GrowMartOrder
    {
        $order = $this->getOrder($orderId);

        $updates = ['status' => $status];

        if ($status === 'delivered') {
            $updates['delivered_at'] = now();
        } elseif ($status === 'cancelled') {
            $updates['cancelled_at'] = now();
        }

        $order->update($updates);

        $order->load('user');
        $this->notificationService->notify(
            $order->user,
            'growmart.order_status',
            'Order Status Update',
            "Order {$order->order_number} is now " . str_replace('_', ' ', $status) . ".",
            route('growmart.orders.show', $order->id),
            'View Order',
            'orders',
            'normal',
            ['order_number' => $order->order_number, 'order_id' => $order->id, 'status' => $status],
        );
        $order->user->notify(new GrowMartOrderNotification('order_status', [
            'order_number' => $order->order_number,
            'order_id' => $order->id,
            'status' => $status,
        ]));

        return $order;
    }

    public function updatePayment(int $orderId, string $paymentStatus): GrowMartOrder
    {
        $order = $this->getOrder($orderId);

        $updates = ['payment_status' => $paymentStatus];
        if ($paymentStatus === 'paid') {
            $updates['paid_at'] = now();
        }

        $order->update($updates);

        if ($paymentStatus === 'paid') {
            $order->load('user');
            $this->notificationService->notify(
                $order->user,
                'growmart.order_paid',
                'Payment Received',
                "Payment received for order {$order->order_number}.",
                route('growmart.orders.show', $order->id),
                'View Order',
                'payments',
                'high',
                ['order_number' => $order->order_number, 'order_id' => $order->id],
            );
            $order->user->notify(new GrowMartOrderNotification('order_paid', [
                'order_number' => $order->orderNumber,
                'order_id' => $order->id,
            ]));
        }

        return $order;
    }

    public function updateTracking(int $orderId, array $data): GrowMartOrder
    {
        $order = $this->getOrder($orderId);

        $updates = [];
        if (isset($data['tracking_number'])) $updates['tracking_number'] = $data['tracking_number'];
        if (isset($data['tracking_url'])) $updates['tracking_url'] = $data['tracking_url'];
        if (isset($data['estimated_delivery_at'])) $updates['estimated_delivery_at'] = $data['estimated_delivery_at'];

        $entry = [
            'status' => $data['tracking_status'] ?? $order->status,
            'message' => $data['tracking_message'] ?? 'Order updated',
            'timestamp' => now()->toISOString(),
        ];

        $existing = $order->tracking_updates ?? [];
        $existing[] = $entry;
        $updates['tracking_updates'] = $existing;

        $order->update($updates);

        return $order;
    }

    public function getAllOrders(array $filters = [], int $perPage = 20)
    {
        $query = GrowMartOrder::with('items', 'user');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $filters['search'] . '%'));
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        $sort = $filters['sort'] ?? 'latest';
        $query->orderBy($sort === 'oldest' ? 'created_at' : 'created_at', $sort === 'oldest' ? 'asc' : 'desc');

        return $query->paginate($perPage);
    }

    private function notifyAdmins(GrowMartOrder $order): void
    {
        $admins = \App\Models\User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new GrowMartOrderNotification('admin_new_order', [
                'order_number' => $order->order_number,
                'order_id' => $order->id,
                'total' => $order->total,
                'customer_name' => $order->user?->name ?? 'Unknown',
            ]));
        }
    }

    private function checkLowStockAfterDeduction(array $items): void
    {
        $productIds = collect($items)->pluck('product_id')->unique();
        $lowStockItems = GrowMartInventory::with('product')
            ->whereIn('product_id', $productIds)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->where(function ($q) {
                $q->whereNull('alert_sent_at')
                    ->orWhere('alert_sent_at', '<', now()->subHours(24));
            })
            ->get();

        if ($lowStockItems->isEmpty()) return;

        $admins = \App\Models\User::role('admin')->get();
        foreach ($lowStockItems as $item) {
            foreach ($admins as $admin) {
                $admin->notify(new LowStockAlertNotification([
                    'product_name' => $item->product?->name ?? 'Unknown',
                    'product_id' => $item->product_id,
                    'inventory_id' => $item->id,
                    'current_stock' => $item->quantity,
                    'threshold' => $item->low_stock_threshold,
                    'warehouse' => $item->warehouse?->name ?? 'Unknown',
                ]));
            }
            $item->update(['alert_sent_at' => now()]);
        }
    }
}
