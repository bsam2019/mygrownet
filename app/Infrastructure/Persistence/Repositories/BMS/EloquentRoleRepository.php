<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Role;
use App\Domain\BMS\Repositories\RoleRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\RoleModel;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function findById(int $id): ?Role
    {
        $model = RoleModel::find($id);
        return $model ? Role::reconstitute($model->toArray()) : null;
    }

    public function save(Role $entity): Role
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            RoleModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = RoleModel::create($data);
        return Role::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return RoleModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Role::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByName(int $companyId, string $name): ?Role
    {
        $model = RoleModel::where('company_id', $companyId)->where('name', $name)->first();
        return $model ? Role::reconstitute($model->toArray()) : null;
    }
}
