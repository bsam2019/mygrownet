<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\Vendor;
use App\Domain\GrowFinance\Repositories\VendorRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceVendorModel;

class EloquentVendorRepository implements VendorRepositoryInterface
{
    public function findById(int $id): ?Vendor
    {
        $model = GrowFinanceVendorModel::find($id);
        return $model ? Vendor::reconstitute($model->toArray()) : null;
    }

    public function save(Vendor $entity): Vendor
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceVendorModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceVendorModel::create($data);
        return Vendor::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceVendorModel::forBusiness($businessId)->get()->map(fn($m) => Vendor::reconstitute($m->toArray()))->toArray();
    }

    public function findActive(int $businessId): array
    {
        return GrowFinanceVendorModel::forBusiness($businessId)->active()->get()->map(fn($m) => Vendor::reconstitute($m->toArray()))->toArray();
    }

    public function findWithOutstanding(int $businessId): array
    {
        return GrowFinanceVendorModel::forBusiness($businessId)->withOutstanding()->get()->map(fn($m) => Vendor::reconstitute($m->toArray()))->toArray();
    }

}
