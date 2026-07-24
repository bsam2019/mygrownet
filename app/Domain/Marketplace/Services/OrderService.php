<?php

namespace App\Domain\Marketplace\Services;

use App\Domain\Marketplace\Entities\Order;
use App\Domain\Marketplace\Repositories\OrderRepositoryInterface;
use App\Domain\Marketplace\Repositories\ProductRepositoryInterface;
use App\Domain\Marketplace\ValueObjects\OrderStatus;
use App\Domain\Marketplace\ValueObjects\DeliveryMethod;
use App\Domain\Marketplace\ValueObjects\Money;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ProductRepositoryInterface $productRepository,
        private EscrowService $escrowService,
        private SellerService $sellerService,
        private \App\Domain\GrowNet\Services\LgrActivityTrackingService $lgrTrackingService,
    ) {}

    public function createOrder(int $buyerId, array $cartItems, array $deliveryData): array
    {
        return DB::transaction(function () use ($buyerId, $cartItems, $deliveryData) {
            $itemsBySeller = collect($cartItems)->groupBy('seller_id');

            if ($itemsBySeller->count() > 1) {
                throw new \Exception('Multi-seller orders not supported yet. Please checkout separately.');
            }

            $sellerId = $itemsBySeller->keys()->first();
            $items = $itemsBySeller->first();

            $subtotal = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = $this->productRepository->findById($item['product_id']);
                if (!$product) {
                    throw new \Exception("Product not found.");
                }

                if (!$product->canBePurchased($item['quantity'])) {
                    throw new \Exception("Product '{$product->name}' is not available in requested quantity.");
                }

                $itemTotal = $product->price->amount() * $item['quantity'];
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price->amount(),
                    'total_price' => $itemTotal,
                ];
            }

            $deliveryFee = $this->calculateDeliveryFee($deliveryData['method'], $deliveryData['province'] ?? null);
            $total = $subtotal + $deliveryFee;

            $order = new Order(
                id: null,
                orderNumber: $this->generateOrderNumber(),
                buyerId: $buyerId,
                sellerId: $sellerId,
                status: OrderStatus::pending(),
                subtotal: Money::fromNgwee($subtotal),
                deliveryFee: Money::fromNgwee($deliveryFee),
                total: Money::fromNgwee($total),
                deliveryMethod: DeliveryMethod::fromString($deliveryData['method']),
                deliveryAddress: [
                    'name' => $deliveryData['name'],
                    'phone' => $deliveryData['phone'],
                    'province' => $deliveryData['province'] ?? null,
                    'district' => $deliveryData['district'] ?? null,
                    'address' => $deliveryData['address'] ?? null,
                ],
                deliveryNotes: $deliveryData['notes'] ?? null,
                items: $orderItems,
                createdAt: new \DateTimeImmutable(),
            );

            $saved = $this->orderRepository->save($order);

            foreach ($orderItems as $item) {
                $this->productRepository->decrementStock($item['product_id'], $item['quantity']);
            }

            return $saved->toArray();
        });
    }

    public function markAsPaid(int $orderId, string $paymentReference): array
    {
        return DB::transaction(function () use ($orderId, $paymentReference) {
            $order = $this->orderRepository->findById($orderId);
            if (!$order || !$order->status->isPending()) {
                throw new \Exception('Order cannot be marked as paid.');
            }

            $this->orderRepository->updateOrderFields($orderId, [
                'status' => 'paid',
                'payment_reference' => $paymentReference,
                'paid_at' => now(),
            ]);

            $this->escrowService->holdFunds($orderId, $order->total->amount());

            $this->lgrTrackingService->recordMarketplacePurchase(
                $order->buyerId,
                $orderId,
                $order->total->toKwacha()
            );

            $updated = $this->orderRepository->findById($orderId);
            return $updated ? $updated->toArray() : [];
        });
    }

    public function markAsShipped(int $orderId, ?string $trackingInfo = null): array
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order || !in_array($order->status->value(), ['paid', 'processing'])) {
            throw new \Exception('Order cannot be marked as shipped.');
        }

        $this->orderRepository->updateOrderFields($orderId, [
            'status' => 'shipped',
            'tracking_info' => $trackingInfo,
            'shipped_at' => now(),
        ]);

        $updated = $this->orderRepository->findById($orderId);
        return $updated ? $updated->toArray() : [];
    }

    public function markAsDelivered(int $orderId, ?string $deliveryProof = null): array
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order || !$order->status->isShipped()) {
            throw new \Exception('Order cannot be marked as delivered.');
        }

        $this->orderRepository->updateOrderFields($orderId, [
            'status' => 'delivered',
            'delivery_proof' => $deliveryProof,
            'delivered_at' => now(),
        ]);

        $updated = $this->orderRepository->findById($orderId);
        return $updated ? $updated->toArray() : [];
    }

    public function confirmReceipt(int $orderId): array
    {
        return DB::transaction(function () use ($orderId) {
            $order = $this->orderRepository->findById($orderId);
            if (!$order || !$order->status->isDelivered()) {
                throw new \Exception('Order cannot be confirmed.');
            }

            $this->orderRepository->updateOrderFields($orderId, [
                'status' => 'completed',
                'confirmed_at' => now(),
            ]);

            $this->escrowService->releaseFunds($orderId, 'buyer_confirmed');

            $this->sellerService->incrementOrderCount($order->sellerId);

            $this->lgrTrackingService->recordMarketplaceSale(
                $order->sellerId,
                $orderId,
                $order->total->toKwacha()
            );

            $updated = $this->orderRepository->findById($orderId);
            return $updated ? $updated->toArray() : [];
        });
    }

    public function cancelOrder(int $orderId, string $reason, string $cancelledBy): array
    {
        return DB::transaction(function () use ($orderId, $reason, $cancelledBy) {
            $order = $this->orderRepository->findById($orderId);
            if (!$order || !$order->canBeCancelled()) {
                throw new \Exception('Order cannot be cancelled at this stage.');
            }

            foreach ($order->items as $item) {
                $this->productRepository->incrementStock($item['product_id'], $item['quantity']);
            }

            if ($order->status->isPaid()) {
                $this->escrowService->refundFunds($orderId, $reason);
            }

            $this->orderRepository->updateOrderFields($orderId, [
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_by' => $cancelledBy,
                'cancelled_at' => now(),
            ]);

            $updated = $this->orderRepository->findById($orderId);
            return $updated ? $updated->toArray() : [];
        });
    }

    public function openDispute(int $orderId, string $reason): array
    {
        $order = $this->orderRepository->findById($orderId);
        if (!$order || !$order->canBeDisputed()) {
            throw new \Exception('Disputes can only be opened for delivered orders.');
        }

        $this->orderRepository->updateOrderFields($orderId, [
            'status' => 'disputed',
            'dispute_reason' => $reason,
            'disputed_at' => now(),
        ]);

        $this->escrowService->markAsDisputed($orderId);

        $updated = $this->orderRepository->findById($orderId);
        return $updated ? $updated->toArray() : [];
    }

    public function getByBuyer(int $buyerId, array $filters = [], int $perPage = 20): array
    {
        return $this->orderRepository->findByBuyer($buyerId, $filters, $perPage);
    }

    public function getBySeller(int $sellerId, array $filters = [], int $perPage = 20): array
    {
        return $this->orderRepository->findBySeller($sellerId, $filters, $perPage);
    }

    public function getById(int $id): ?array
    {
        $order = $this->orderRepository->findById($id);
        return $order ? $order->toArray() : null;
    }

    public function processAutoReleases(): int
    {
        $orders = $this->orderRepository->findPendingAutoRelease();
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
        } while ($this->orderRepository->orderNumberExists($number));

        return $number;
    }

    private function calculateDeliveryFee(string $method, ?string $province): int
    {
        return match ($method) {
            'pickup' => 0,
            'self' => 2500,
            'courier' => 5000,
            default => 0,
        };
    }
}
