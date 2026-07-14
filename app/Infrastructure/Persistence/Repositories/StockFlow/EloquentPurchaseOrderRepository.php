<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\PurchaseOrder;
use App\Domain\StockFlow\Entities\PurchaseOrderItem;
use App\Domain\StockFlow\Repositories\PurchaseOrderRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\PurchaseOrderId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\SupplierId;
use App\Domain\StockFlow\ValueObjects\ItemId;
use App\Domain\StockFlow\ValueObjects\Money;
use App\Domain\StockFlow\ValueObjects\PurchaseOrderStatus;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaPurchaseOrderModel;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaPurchaseOrderItemModel;
use DateTimeImmutable;

class EloquentPurchaseOrderRepository implements PurchaseOrderRepositoryInterface
{
    public function findById(PurchaseOrderId $id): ?PurchaseOrder
    {
        $model = SaPurchaseOrderModel::with('items.item', 'supplier')->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyIdAndDateBetween(CompanyId $companyId, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        return SaPurchaseOrderModel::where('sa_company_id', $companyId->toInt())
            ->whereBetween('order_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->with('items.item', 'supplier')
            ->orderBy('order_date', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByCompanyId(CompanyId $companyId, int $perPage = 20): array
    {
        return SaPurchaseOrderModel::where('sa_company_id', $companyId->toInt())
            ->with('items.item')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByCompanyIdAndStatus(CompanyId $companyId, string $status): array
    {
        return SaPurchaseOrderModel::where('sa_company_id', $companyId->toInt())
            ->where('status', $status)
            ->with('items.item')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(PurchaseOrder $order): PurchaseOrder
    {
        $data = [
            'sa_company_id' => $order->getCompanyId()->toInt(),
            'sa_supplier_id' => $order->getSupplierId()?->toInt(),
            'order_number' => $order->getOrderNumber(),
            'order_date' => $order->getOrderDate()->format('Y-m-d'),
            'status' => $order->getStatus()->value(),
            'subtotal' => $order->getSubtotal()->toFloat(),
            'tax' => 0,
            'total' => $order->getTotal()->toFloat(),
            'notes' => $order->getNotes(),
        ];

        if ($order->id() === 0) {
            $model = SaPurchaseOrderModel::create($data);
        } else {
            $model = SaPurchaseOrderModel::find($order->id());
            $model->update($data);
        }

        // Persist PO items
        if ($order->id() === 0) {
            \App\Infrastructure\Persistence\Eloquent\StockFlow\SaPurchaseOrderItemModel::where('sa_purchase_order_id', $model->id)->delete();
        }
        foreach ($order->getItems() as $item) {
            \App\Infrastructure\Persistence\Eloquent\StockFlow\SaPurchaseOrderItemModel::create([
                'sa_purchase_order_id' => $model->id,
                'sa_item_id' => $item->getItemId()->toInt(),
                'quantity_ordered' => $item->getQuantityOrdered(),
                'quantity_received' => $item->getQuantityReceived(),
                'unit_cost' => $item->getUnitCost()->toFloat(),
                'total_cost' => $item->getUnitCost()->multiply($item->getQuantityOrdered())->toFloat(),
            ]);
        }

        return $this->toDomainEntity($model->fresh());
    }

    public function nextOrderNumber(): string
    {
        return 'PO-' . str_pad($this->getMaxId() + 1, 6, '0', STR_PAD_LEFT);
    }

    public function getMaxId(): int
    {
        return (int) SaPurchaseOrderModel::max('id');
    }

    private function toDomainEntity(SaPurchaseOrderModel $model): PurchaseOrder
    {
        $order = PurchaseOrder::reconstitute(
            id: PurchaseOrderId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            supplierId: $model->sa_supplier_id ? SupplierId::fromInt($model->sa_supplier_id) : null,
            orderNumber: $model->order_number,
            orderDate: new DateTimeImmutable($model->order_date->format('Y-m-d')),
            status: PurchaseOrderStatus::fromString($model->status),
            subtotal: Money::fromFloat((float) $model->subtotal),
            tax: Money::fromFloat((float) ($model->tax ?? 0)),
            total: Money::fromFloat((float) $model->total),
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );

        if ($model->relationLoaded('items')) {
            foreach ($model->items as $itemModel) {
                $poItem = PurchaseOrderItem::reconstitute(
                    id: new \App\Domain\StockFlow\ValueObjects\PurchaseOrderItemId($itemModel->id),
                    purchaseOrderId: PurchaseOrderId::fromInt($itemModel->sa_purchase_order_id),
                    itemId: ItemId::fromInt($itemModel->sa_item_id),
                    quantityOrdered: (float) $itemModel->quantity_ordered,
                    quantityReceived: (float) $itemModel->quantity_received,
                    unitCost: Money::fromFloat((float) $itemModel->unit_cost),
                    totalCost: Money::fromFloat((float) $itemModel->total_cost),
                    itemName: $itemModel->item?->name,
                    createdAt: new DateTimeImmutable($itemModel->created_at->format('Y-m-d H:i:s')),
                    updatedAt: new DateTimeImmutable($itemModel->updated_at->format('Y-m-d H:i:s')),
                );
                $order->addItem($poItem);
            }
        }

        if ($model->relationLoaded('supplier') && $model->supplier) {
            $order->setSupplierName($model->supplier->name);
        }

        return $order;
    }
}
