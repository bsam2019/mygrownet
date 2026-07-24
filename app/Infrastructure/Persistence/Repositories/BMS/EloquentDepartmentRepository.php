<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Department;
use App\Domain\BMS\Repositories\DepartmentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\DepartmentModel;

class EloquentDepartmentRepository implements DepartmentRepositoryInterface
{
    public function findById(int $id): ?Department
    {
        $model = DepartmentModel::find($id);
        return $model ? Department::reconstitute($model->toArray()) : null;
    }

    public function save(Department $entity): Department
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            DepartmentModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = DepartmentModel::create($data);
        return Department::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return DepartmentModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Department::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $companyId): array
    {
        return DepartmentModel::where('company_id', $companyId)->where('is_active', true)->get()
            ->map(fn($m) => Department::reconstitute($m->toArray()))
            ->toArray();
    }
}
