<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Investment;

use App\Domain\Investment\Entities\InvestmentCategory;
use App\Domain\Investment\Repositories\InvestmentCategoryRepositoryInterface;
use App\Models\InvestmentCategory as InvestmentCategoryModel;

class EloquentInvestmentCategoryRepository implements InvestmentCategoryRepositoryInterface
{
    public function findById(int $id): ?InvestmentCategory
    {
        $model = InvestmentCategoryModel::find($id);
        return $model ? InvestmentCategory::reconstitute($model->toArray()) : null;
    }

    public function save(InvestmentCategory $entity): InvestmentCategory
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            InvestmentCategoryModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = InvestmentCategoryModel::create($data);
        return InvestmentCategory::reconstitute($model->toArray());
    }

    public function findActive(): array
    {
        return InvestmentCategoryModel::where('is_active', true)
            ->get()
            ->map(fn($m) => InvestmentCategory::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findBySlug(string $slug): ?InvestmentCategory
    {
        $model = InvestmentCategoryModel::where('slug', $slug)->first();
        return $model ? InvestmentCategory::reconstitute($model->toArray()) : null;
    }
}
