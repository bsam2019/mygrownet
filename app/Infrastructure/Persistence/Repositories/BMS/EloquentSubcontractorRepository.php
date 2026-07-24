<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Subcontractor;
use App\Domain\BMS\Repositories\SubcontractorRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\SubcontractorModel;

class EloquentSubcontractorRepository implements SubcontractorRepositoryInterface
{
    public function findById(int $id): ?Subcontractor
    {
        $model = SubcontractorModel::find($id);
        return $model ? Subcontractor::reconstitute($model->toArray()) : null;
    }

    public function save(Subcontractor $entity): Subcontractor
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            SubcontractorModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = SubcontractorModel::create($data);
        return Subcontractor::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return SubcontractorModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Subcontractor::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $companyId): array
    {
        return SubcontractorModel::where('company_id', $companyId)->where('status', 'active')->get()
            ->map(fn($m) => Subcontractor::reconstitute($m->toArray()))
            ->toArray();
    }
}
