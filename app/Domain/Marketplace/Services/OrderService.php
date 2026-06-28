<?php

namespace App\Domain\Marketplace\Services;

use App\Models\MarketplaceOrder;
use App\Models\MarketplaceOrderItem;
use App\Models\MarketplaceProduct;
use App\Domain\Marketplace\Services\EscrowService;
use App\Domain\Marketplace\Services\ProductService;
use App\Domain\Marketplace\Services\SellerService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderService
{
    public function __construct(
        private EscrowService $escrowService,
        private ProductService $productService,
        private SellerService $sellerService,
        private \App\Services\LgrActivityTrackingService $lgrTrackingService,
    ) {}

    public function createOrder(int $buyerId, array $cartItems, array $deliveryData): MarketplaceOrder
    {
        return DB::transaction(function () use ($buyerId, $cartItems, $deliveryData) {
            // Group items by seller
            $itemsBySeller = collect($cartItems)->groupBy('seller_id');
            
            // For MVP, we only support single-seller orders
            if ($itemsBySeller->count() > 1) {
                throw new \Exception('Multi-seller orders not supported yet. Please checkout separately.');
            }

            $sellerId = $itemsBySeller->keys()->first();
            $items = $itemsBySeller->first();

            // Calculate totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = MarketplaceProduct::findOrFail($item['product_id']);
                
                if (!$product->canBePurchased($item['quantity'])) {
                    throw new \Exception("Product '{$product->name}' is not available in requested quantity.");
                }

                $itemTotal = $product->price * $item['quantity'];
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $itemTotal,
                ];
            }

            $deliveryFee = $this->calculateDeliveryFee($deliveryData['method'], $deliveryData['province'] ?? null);
            $total = $subtotal + $deliveryFee;

            // Create order
            $order = MarketplaceOrder::create([
                'order_number' => $this->generateOrderNumber(),
                'buyer_id' => $buyerId,
                'seller_id' => $sellerId,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'delivery_method' => $deliveryData['method'],
                'delivery_address' => [
                    'name' => $deliveryData['name'],
                    'phone' => $deliveryData['phone'],
                    'province' => $deliveryData['province'] ?? null,
                    'district' => $deliveryData['district'] ?? null,
                    'address' => $deliveryData['address'] ?? null,
                ],
                'delivery_notes' => $deliveryData['notes'] ?? null,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);
                
                // Reserve stock
                $this->productService->decrementStock($item['product_id'], $item['quantity']);
            }

            return $order->load(['items.product', 'seller', 'buyer']);
        });
    }

    public function markAsPaid(int $orderId, string $paymentReference): MarketplaceOrder
    {
        return DB::transaction(function () use ($orderId, $paymentReference) {
            $order = MarketplaceOrder::findOrFail($orderId);
            
            if ($order->status !== 'pending') {
                throw new \Exception('Order cannot be marked as paid.');
            }

            $order->update([
                'status' => 'paid',
                'payment_reference' => $paymentReference,
                'paid_at' => now(),
            ]);

            // Create escrow hold
            $this->escrowService->holdFunds($order);

            // CRITICAL: Record LGR activity for buyer
            $this->lgrTrackingService->recordMarketplacePurchase(
                $order->buyer_id,
                $order->id,
                $order->total
            );

            return $order->fresh();
        });
    }

    public function markAsShipped(int $orderId, ?string $trackingInfo = null): MarketplaceOrder
    {
        $order = MarketplaceOrder::findOrFail($orderId);
        
        if ($order->status !== 'paid' && $order->status !== 'processing') {
            throw new \Exception('Order cannot be marked as shipped.');
        }

        $order->update([
            'status' => 'shipped',
            'tracking_info' => $trackingInfo,
            'shipped_at' => now(),
        ]);

        return $order->fresh();
    }

    public function markAsDelivered(int $orderId, ?string $deliveryProof = null): MarketplaceOrder
    {
        $order = MarketplaceOrder::findOrFail($orderId);
        
        if ($order->status !== 'shipped') {
            throw new \Exception('Order cannot be marked as delivered.');
        }

        $order->update([
            'status' => 'delivered',
            'delivery_proof' => $deliveryProof,
            'delivered_at' => now(),
        ]);

        return $order->fresh();
    }

    public function confirmReceipt(int $orderId): MarketplaceOrder
    {
        return DB::transaction(function () use ($orderId) {
            $order = MarketplaceOrder::findOrFail($orderId);
            
            if ($order->status !== 'delivered') {
                throw new \Exception('Order cannot be confirmed.');
            }

            $order->update([
                'status' => 'completed',
                'confirmed_at' => now(),
            ]);

            // Release escrow funds to seller
            $this->escrowService->releaseFunds($order, 'buyer_confirmed');

            // Update seller stats
            $this->sellerService->incrementOrderCount($order->seller_id);

            // CRITICAL: Record LGR activity for seller
            $this->lgrTrackingService->recordMarketplaceSale(
                $order->seller_id,
                $order->id,
                $order->total
            );

            return $order->fresh();
        });
    }

    public function cancelOrder(int $orderId, string $reason, string $cancelledBy): MarketplaceOrder
    {
        return DB::transaction(function () use ($orderId, $reason, $cancelledBy) {
            $order = MarketplaceOrder::findOrFail($orderId);
            
            if (!in_array($order->status, ['pending', 'paid'])) {
                throw new \Exception('Order cannot be cancelled at this stage.');
            }

            // Restore stock
            foreach ($order->items as $item) {
                $this->productService->incrementStock($item->product_id, $item->quantity);
            }

            // Refund if paid
            if ($order->status === 'paid') {
                $this->escrowService->refundFunds($order, $reason);
            }

            $order->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_by' => $cancelledBy,
                'cancelled_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    public function openDispute(int $orderId, string $reason): MarketplaceOrder
    {
        $order = MarketplaceOrder::findOrFail($orderId);
        
        if ($order->status !== 'delivered') {
            throw new \Exception('Disputes can only be opened for delivered orders.');
        }

        $order->update([
            'status' => 'disputed',
            'dispute_reason' => $reason,
            'disputed_at' => now(),
        ]);

        $this->escrowService->markAsDisputed($order);

        return $order->fresh();
    }

    public function getByBuyer(int $buyerId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MarketplaceOrder::with(['items.product', 'seller'])
            ->where('buyer_id', $buyerId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function getBySeller(int $sellerId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = MarketplaceOrder::with(['items.product', 'buyer'])
            ->where('seller_id', $sellerId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function getById(int $id): ?MarketplaceOrder
    {
        return MarketplaceOrder::with(['items.product', 'seller.user', 'buyer', 'escrow'])
            ->find($id);
    }

    public function processAutoReleases(): int
    {
        $orders = MarketplaceOrder::where('status', 'delivered')
            ->where('delivered_at', '<=', now()->subDays(7))
            ->whereNull('confirmed_at')
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            try {
                $this->confirmReceipt($order->id);
                $count++;
            } catch (\Exception $e) {
                \Log::error("Auto-release failed for order {$order->id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'MKT-' . strtoupper(Str::random(8));
        } while (MarketplaceOrder::where('order_number', $number)->exists());

        return $number;
    }

    private function calculateDeliveryFee(string $method, ?string $province): int
    {
        // Simple delivery fee calculation for MVP
        return match ($method) {
            'pickup' => 0,
            'self' => 2500, // K25 flat rate for seller delivery
            'courier' => 5000, // K50 flat rate for courier
            default => 0,
        };
    }
}
