<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\MaterialCategory;
use App\Domain\BMS\Repositories\MaterialCategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\MaterialCategoryModel;

class EloquentMaterialCategoryRepository implements MaterialCategoryRepositoryInterface
{
    public function findById(int $id): ?MaterialCategory
    {
        $model = MaterialCategoryModel::find($id);
        return $model ? MaterialCategory::reconstitute($model->toArray()) : null;
    }

    public function save(MaterialCategory $entity): MaterialCategory
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            MaterialCategoryModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = MaterialCategoryModel::create($data);
        return MaterialCategory::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return MaterialCategoryModel::where('company_id', $companyId)->get()
            ->map(fn($m) => MaterialCategory::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $companyId): array
    {
        return MaterialCategoryModel::where('company_id', $companyId)->where('is_active', true)->get()
            ->map(fn($m) => MaterialCategory::reconstitute($m->toArray()))
            ->toArray();
    }
}
