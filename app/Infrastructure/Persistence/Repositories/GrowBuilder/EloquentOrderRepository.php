<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Order;
use App\Domain\GrowBuilder\Repositories\OrderRepositoryInterface;
use App\Domain\GrowBuilder\ValueObjects\Money;
use App\Domain\GrowBuilder\ValueObjects\OrderId;
use App\Domain\GrowBuilder\ValueObjects\OrderStatus;
use App\Domain\GrowBuilder\ValueObjects\SiteId;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderOrder;
use DateTimeImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findById(OrderId $id): ?Order
    {
        $model = GrowBuilderOrder::find($id->value());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySiteId(SiteId $siteId): array
    {
        return GrowBuilderOrder::where('site_id', $siteId->value())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findBySiteIdPaginated(SiteId $siteId, int $perPage = 20, ?string $status = null, ?int $siteUserId = null): LengthAwarePaginator
    {
        $query = GrowBuilderOrder::where('site_id', $siteId->value());

        if ($status) {
            $query->where('status', $status);
        }

        if ($siteUserId) {
            $query->where('site_user_id', $siteUserId);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findByIdForSite(OrderId $id, SiteId $siteId): ?Order
    {
        $model = GrowBuilderOrder::where('site_id', $siteId->value())
            ->where('id', $id->value())
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function countBySiteId(SiteId $siteId): int
    {
        return GrowBuilderOrder::where('site_id', $siteId->value())->count();
    }

    public function countByStatus(SiteId $siteId, string $status): int
    {
        return GrowBuilderOrder::where('site_id', $siteId->value())
            ->where('status', $status)
            ->count();
    }

    public function sumTotalBySiteId(SiteId $siteId): int
    {
        return (int) GrowBuilderOrder::where('site_id', $siteId->value())
            ->whereIn('status', ['paid', 'processing', 'completed'])
            ->sum('total');
    }

    public function sumTotalPaidBySiteId(SiteId $siteId): int
    {
        return (int) GrowBuilderOrder::where('site_id', $siteId->value())
            ->where('status', 'paid')
            ->sum('total');
    }

    public function save(Order $order): Order
    {
        $data = [
            'site_id' => $order->getSiteId(),
            'order_number' => $order->getOrderNumber(),
            'customer_name' => $order->getCustomerName(),
            'customer_phone' => $order->getCustomerPhone(),
            'customer_email' => $order->getCustomerEmail(),
            'customer_address' => $order->getCustomerAddress(),
            'customer_city' => $order->getCustomerCity(),
            'items' => $order->getItems(),
            'subtotal' => $order->getSubtotal()->getAmountInNgwee(),
            'shipping_cost' => $order->getShippingCost()->getAmountInNgwee(),
            'discount_amount' => $order->getDiscountAmount()->getAmountInNgwee(),
            'discount_code' => $order->getDiscountCode(),
            'total' => $order->getTotal()->getAmountInNgwee(),
            'status' => $order->getStatus()->value(),
            'payment_method' => $order->getPaymentMethod(),
            'payment_reference' => $order->getPaymentReference(),
            'notes' => $order->getNotes(),
            'admin_notes' => $order->getAdminNotes(),
            'paid_at' => $order->getPaidAt()?->format('Y-m-d H:i:s'),
            'shipped_at' => $order->getShippedAt()?->format('Y-m-d H:i:s'),
            'delivered_at' => $order->getDeliveredAt()?->format('Y-m-d H:i:s'),
        ];

        if ($order->getId()) {
            $model = GrowBuilderOrder::findOrFail($order->getId()->value());
            $model->update($data);
        } else {
            $model = GrowBuilderOrder::create($data);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function delete(OrderId $id): void
    {
        GrowBuilderOrder::destroy($id->value());
    }

    private function toDomainEntity(GrowBuilderOrder $model): Order
    {
        return Order::reconstitute(
            id: OrderId::fromInt($model->id),
            siteId: $model->site_id,
            orderNumber: $model->order_number,
            customerName: $model->customer_name,
            customerPhone: $model->customer_phone,
            customerEmail: $model->customer_email,
            customerAddress: $model->customer_address,
            customerCity: $model->customer_city,
            items: $model->items ?? [],
            subtotal: Money::fromNgwee($model->subtotal ?? 0),
            shippingCost: Money::fromNgwee($model->shipping_cost ?? 0),
            discountAmount: Money::fromNgwee($model->discount_amount ?? 0),
            discountCode: $model->discount_code,
            total: Money::fromNgwee($model->total ?? 0),
            status: OrderStatus::fromString($model->status ?? 'pending'),
            paymentMethod: $model->payment_method,
            paymentReference: $model->payment_reference,
            notes: $model->notes,
            adminNotes: $model->admin_notes,
            paidAt: $model->paid_at ? new DateTimeImmutable($model->paid_at->toDateTimeString()) : null,
            shippedAt: $model->shipped_at ? new DateTimeImmutable($model->shipped_at->toDateTimeString()) : null,
            deliveredAt: $model->delivered_at ? new DateTimeImmutable($model->delivered_at->toDateTimeString()) : null,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
