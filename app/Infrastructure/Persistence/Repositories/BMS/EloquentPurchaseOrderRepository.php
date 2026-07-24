<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\PurchaseOrder;
use App\Domain\BMS\Repositories\PurchaseOrderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\PurchaseOrderModel;

class EloquentPurchaseOrderRepository implements PurchaseOrderRepositoryInterface
{
    public function findById(int $id): ?PurchaseOrder
    {
        $model = PurchaseOrderModel::find($id);
        return $model ? PurchaseOrder::reconstitute($model->toArray()) : null;
    }

    public function save(PurchaseOrder $entity): PurchaseOrder
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            PurchaseOrderModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = PurchaseOrderModel::create($data);
        return PurchaseOrder::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return PurchaseOrderModel::where('company_id', $companyId)->get()
            ->map(fn($m) => PurchaseOrder::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByVendor(int $vendorId): array
    {
        return PurchaseOrderModel::where('vendor_id', $vendorId)->get()
            ->map(fn($m) => PurchaseOrder::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByStatus(int $companyId, string $status): array
    {
        return PurchaseOrderModel::where('company_id', $companyId)->where('status', $status)->get()
            ->map(fn($m) => PurchaseOrder::reconstitute($m->toArray()))
            ->toArray();
    }
}
