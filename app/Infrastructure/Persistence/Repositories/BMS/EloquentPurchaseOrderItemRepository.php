<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\PurchaseOrderItem;
use App\Domain\BMS\Repositories\PurchaseOrderItemRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\PurchaseOrderItemModel;

class EloquentPurchaseOrderItemRepository implements PurchaseOrderItemRepositoryInterface
{
    public function findById(int $id): ?PurchaseOrderItem
    {
        $model = PurchaseOrderItemModel::find($id);
        return $model ? PurchaseOrderItem::reconstitute($model->toArray()) : null;
    }

    public function save(PurchaseOrderItem $entity): PurchaseOrderItem
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            PurchaseOrderItemModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = PurchaseOrderItemModel::create($data);
        return PurchaseOrderItem::reconstitute($model->toArray());
    }

    public function findByPurchaseOrder(int $purchaseOrderId): array
    {
        return PurchaseOrderItemModel::where('purchase_order_id', $purchaseOrderId)->get()
            ->map(fn($m) => PurchaseOrderItem::reconstitute($m->toArray()))
            ->toArray();
    }

    public function deleteByPurchaseOrder(int $purchaseOrderId): void
    {
        PurchaseOrderItemModel::where('purchase_order_id', $purchaseOrderId)->delete();
    }
}
