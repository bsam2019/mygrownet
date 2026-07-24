<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Vendor;
use App\Domain\BMS\Repositories\VendorRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\VendorModel;

class EloquentVendorRepository implements VendorRepositoryInterface
{
    public function findById(int $id): ?Vendor
    {
        $model = VendorModel::find($id);
        return $model ? Vendor::reconstitute($model->toArray()) : null;
    }

    public function save(Vendor $entity): Vendor
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            VendorModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = VendorModel::create($data);
        return Vendor::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return VendorModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Vendor::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $companyId): array
    {
        return VendorModel::where('company_id', $companyId)->where('status', 'active')->get()
            ->map(fn($m) => Vendor::reconstitute($m->toArray()))
            ->toArray();
    }
}
