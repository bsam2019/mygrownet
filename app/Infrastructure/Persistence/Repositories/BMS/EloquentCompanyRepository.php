<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Company;
use App\Domain\BMS\Repositories\CompanyRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\CompanyModel;

class EloquentCompanyRepository implements CompanyRepositoryInterface
{
    public function findById(int $id): ?Company
    {
        $model = CompanyModel::find($id);
        return $model ? Company::reconstitute($model->toArray()) : null;
    }

    public function save(Company $entity): Company
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            CompanyModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = CompanyModel::create($data);
        return Company::reconstitute($model->toArray());
    }

    public function findByOrganization(int $organizationId): array
    {
        return CompanyModel::where('organization_id', $organizationId)->get()
            ->map(fn($m) => Company::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findAll(): array
    {
        return CompanyModel::all()->map(fn($m) => Company::reconstitute($m->toArray()))->toArray();
    }

    public function findActive(): array
    {
        return CompanyModel::where('status', 'active')->get()
            ->map(fn($m) => Company::reconstitute($m->toArray()))
            ->toArray();
    }
}
