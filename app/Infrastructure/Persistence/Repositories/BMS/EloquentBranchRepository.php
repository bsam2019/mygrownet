<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Branch;
use App\Domain\BMS\Repositories\BranchRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\BranchModel;

class EloquentBranchRepository implements BranchRepositoryInterface
{
    public function findById(int $id): ?Branch
    {
        $model = BranchModel::find($id);
        return $model ? Branch::reconstitute($model->toArray()) : null;
    }

    public function save(Branch $entity): Branch
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BranchModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BranchModel::create($data);
        return Branch::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return BranchModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Branch::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $companyId): array
    {
        return BranchModel::where('company_id', $companyId)->where('is_active', true)->get()
            ->map(fn($m) => Branch::reconstitute($m->toArray()))
            ->toArray();
    }
}
