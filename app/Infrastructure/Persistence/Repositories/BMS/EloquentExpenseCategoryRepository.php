<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\ExpenseCategory;
use App\Domain\BMS\Repositories\ExpenseCategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\ExpenseCategoryModel;

class EloquentExpenseCategoryRepository implements ExpenseCategoryRepositoryInterface
{
    public function findById(int $id): ?ExpenseCategory
    {
        $model = ExpenseCategoryModel::find($id);
        return $model ? ExpenseCategory::reconstitute($model->toArray()) : null;
    }

    public function save(ExpenseCategory $entity): ExpenseCategory
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            ExpenseCategoryModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = ExpenseCategoryModel::create($data);
        return ExpenseCategory::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return ExpenseCategoryModel::where('company_id', $companyId)->get()
            ->map(fn($m) => ExpenseCategory::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $companyId): array
    {
        return ExpenseCategoryModel::where('company_id', $companyId)->where('is_active', true)->get()
            ->map(fn($m) => ExpenseCategory::reconstitute($m->toArray()))
            ->toArray();
    }
}
