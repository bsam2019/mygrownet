<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Material;
use App\Domain\BMS\Repositories\MaterialRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\MaterialModel;

class EloquentMaterialRepository implements MaterialRepositoryInterface
{
    public function findById(int $id): ?Material
    {
        $model = MaterialModel::find($id);
        return $model ? Material::reconstitute($model->toArray()) : null;
    }

    public function save(Material $entity): Material
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            MaterialModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = MaterialModel::create($data);
        return Material::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return MaterialModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Material::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByCategory(int $categoryId): array
    {
        return MaterialModel::where('category_id', $categoryId)->get()
            ->map(fn($m) => Material::reconstitute($m->toArray()))
            ->toArray();
    }
}
