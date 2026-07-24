<?php

namespace App\Infrastructure\Persistence\Repositories\Marketplace;

use App\Domain\Marketplace\Entities\Order;
use App\Domain\Marketplace\Repositories\OrderRepositoryInterface;
use App\Domain\Marketplace\ValueObjects\OrderStatus;
use App\Domain\Marketplace\ValueObjects\DeliveryMethod;
use App\Domain\Marketplace\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceOrder;
use App\Infrastructure\Persistence\Eloquent\Marketplace\MarketplaceOrderItem;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findById(int $id): ?Order
    {
        $model = MarketplaceOrder::with('items')->find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        $model = MarketplaceOrder::with('items')->where('order_number', $orderNumber)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByBuyer(int $buyerId, array $filters = [], int $perPage = 20): array
    {
        $query = MarketplaceOrder::with('items')->where('buyer_id', $buyerId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->toArray()['data'];
    }

    public function findBySeller(int $sellerId, array $filters = [], int $perPage = 20): array
    {
        $query = MarketplaceOrder::with('items')->where('seller_id', $sellerId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->toArray()['data'];
    }

    public function findPendingAutoRelease(): array
    {
        $models = MarketplaceOrder::with('items')
            ->where('status', 'delivered')
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', now()->subDays(7))
            ->get();

        return $models->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function save(Order $order): Order
    {
        $data = [
            'order_number' => $order->orderNumber,
            'buyer_id' => $order->buyerId,
            'seller_id' => $order->sellerId,
            'status' => $order->status->value(),
            'subtotal' => $order->subtotal->amount(),
            'delivery_fee' => $order->deliveryFee->amount(),
            'total' => $order->total->amount(),
            'delivery_method' => $order->deliveryMethod->value(),
            'delivery_address' => $order->deliveryAddress,
            'delivery_notes' => $order->deliveryNotes,
        ];

        if ($order->id) {
            MarketplaceOrder::where('id', $order->id)->update($data);
            // Sync items: delete existing, then re-create
            MarketplaceOrderItem::where('order_id', $order->id)->delete();
            foreach ($order->items as $item) {
                MarketplaceOrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }
            return $this->findById($order->id);
        }

        $model = MarketplaceOrder::create($data);
        foreach ($order->items as $item) {
            MarketplaceOrderItem::create(array_merge($item, ['order_id' => $model->id]));
        }
        return $this->findById($model->id);
    }

    public function updateStatus(int $orderId, string $status): void
    {
        MarketplaceOrder::where('id', $orderId)->update(['status' => $status]);
    }

    public function updateOrderFields(int $orderId, array $data): void
    {
        MarketplaceOrder::where('id', $orderId)->update($data);
    }

    public function markAsDelivered(int $orderId): void
    {
        MarketplaceOrder::where('id', $orderId)->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function markAsConfirmed(int $orderId): void
    {
        MarketplaceOrder::where('id', $orderId)->update([
            'status' => 'completed',
            'confirmed_at' => now(),
        ]);
    }

    public function orderNumberExists(string $orderNumber): bool
    {
        return MarketplaceOrder::where('order_number', $orderNumber)->exists();
    }

    private function toDomainEntity(MarketplaceOrder $model): Order
    {
        $items = $model->relationLoaded('items')
            ? $model->items->map(fn($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ])->all()
            : [];

        return new Order(
            id: $model->id,
            orderNumber: $model->order_number,
            buyerId: $model->buyer_id,
            sellerId: $model->seller_id,
            status: OrderStatus::fromString($model->status),
            subtotal: Money::fromNgwee($model->subtotal),
            deliveryFee: Money::fromNgwee($model->delivery_fee),
            total: Money::fromNgwee($model->total),
            deliveryMethod: DeliveryMethod::fromString($model->delivery_method),
            deliveryAddress: $model->delivery_address ?? [],
            deliveryNotes: $model->delivery_notes,
            items: $items,
            deliveredAt: $model->delivered_at ? new \DateTimeImmutable($model->delivered_at) : null,
            confirmedAt: $model->confirmed_at ? new \DateTimeImmutable($model->confirmed_at) : null,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at) : null,
        );
    }
}
