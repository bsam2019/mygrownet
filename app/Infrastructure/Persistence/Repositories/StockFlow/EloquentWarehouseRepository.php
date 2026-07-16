<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\Warehouse;
use App\Domain\StockFlow\Repositories\WarehouseRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\WarehouseId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaWarehouseModel;
use DateTimeImmutable;

class EloquentWarehouseRepository implements WarehouseRepositoryInterface
{
    public function findById(WarehouseId $id): ?Warehouse
    {
        $model = SaWarehouseModel::find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId): array
    {
        return SaWarehouseModel::where('sa_company_id', $companyId->toInt())->orderBy('name')->get()->map(fn($m) => $this->toDomainEntity($m))->all();
    }

    public function findDefault(CompanyId $companyId): ?Warehouse
    {
        $model = SaWarehouseModel::where('sa_company_id', $companyId->toInt())->where('is_default', true)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(Warehouse $warehouse): Warehouse
    {
        $data = $warehouse->toArray();
        unset($data['id'], $data['created_at'], $data['updated_at']);
        if ($warehouse->id() === 0) {
            $model = SaWarehouseModel::create($data);
        } else {
            $model = SaWarehouseModel::find($warehouse->id());
            $model->update($data);
        }
        return $this->toDomainEntity($model->fresh());
    }

    public function delete(WarehouseId $id): void
    {
        SaWarehouseModel::destroy($id->toInt());
    }

    private function toDomainEntity(SaWarehouseModel $model): Warehouse
    {
        return Warehouse::reconstitute(
            id: WarehouseId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            name: $model->name,
            code: $model->code,
            address: $model->address,
            city: $model->city,
            country: $model->country,
            contactPerson: $model->contact_person,
            phone: $model->phone,
            isDefault: (bool) $model->is_default,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}
