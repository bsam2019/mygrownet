<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\SupplierReturn;
use App\Domain\StockFlow\Repositories\SupplierReturnRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\SupplierReturnId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaSupplierReturnModel;
use DateTimeImmutable;

class EloquentSupplierReturnRepository implements SupplierReturnRepositoryInterface
{
    public function findById(SupplierReturnId $id): ?SupplierReturn
    {
        $model = SaSupplierReturnModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaSupplierReturnModel::where('sa_company_id', $companyId->toInt())->orderByDesc('created_at')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function save(SupplierReturn $return): SupplierReturn
    {
        $data = $return->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        if ($return->id() === 0) {
            $model = SaSupplierReturnModel::create($data);
        } else {
            $model = SaSupplierReturnModel::find($return->id());
            $model->update($data);
        }
        return $this->toDomainEntity($model->fresh());
    }

    public function delete(SupplierReturnId $id): void { SaSupplierReturnModel::destroy($id->toInt()); }

    private function toDomainEntity(SaSupplierReturnModel $model): SupplierReturn
    {
        return SupplierReturn::reconstitute(
            id: SupplierReturnId::fromInt($model->id), companyId: CompanyId::fromInt($model->sa_company_id),
            supplierId: \App\Domain\StockFlow\ValueObjects\SupplierId::fromInt($model->sa_supplier_id),
            purchaseOrderId: $model->sa_purchase_order_id ? \App\Domain\StockFlow\ValueObjects\PurchaseOrderId::fromInt($model->sa_purchase_order_id) : null,
            returnNumber: $model->return_number, returnDate: new DateTimeImmutable($model->return_date->format('Y-m-d')),
            reason: $model->reason, totalRefund: (float) $model->total_refund,
            notes: $model->notes, createdBy: \App\Domain\StockFlow\ValueObjects\UserId::fromInt($model->created_by),
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
