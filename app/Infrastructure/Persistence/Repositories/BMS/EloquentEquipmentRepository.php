<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Equipment;
use App\Domain\BMS\Repositories\EquipmentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\EquipmentModel;

class EloquentEquipmentRepository implements EquipmentRepositoryInterface
{
    public function findById(int $id): ?Equipment
    {
        $model = EquipmentModel::find($id);
        return $model ? Equipment::reconstitute($model->toArray()) : null;
    }

    public function save(Equipment $entity): Equipment
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            EquipmentModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = EquipmentModel::create($data);
        return Equipment::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return EquipmentModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Equipment::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $companyId): array
    {
        return EquipmentModel::where('company_id', $companyId)->where('status', 'active')->get()
            ->map(fn($m) => Equipment::reconstitute($m->toArray()))
            ->toArray();
    }
}
