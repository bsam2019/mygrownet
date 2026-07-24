<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\Budget;
use App\Domain\GrowFinance\Repositories\BudgetRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceBudgetModel;

class EloquentBudgetRepository implements BudgetRepositoryInterface
{
    public function findById(int $id): ?Budget
    {
        $model = GrowFinanceBudgetModel::find($id);
        return $model ? Budget::reconstitute($model->toArray()) : null;
    }

    public function save(Budget $entity): Budget
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceBudgetModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceBudgetModel::create($data);
        return Budget::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceBudgetModel::forBusiness($businessId)->get()
            ->map(fn($m) => Budget::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $businessId): array
    {
        return GrowFinanceBudgetModel::forBusiness($businessId)->active()->get()
            ->map(fn($m) => Budget::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findCurrent(int $businessId): array
    {
        return GrowFinanceBudgetModel::forBusiness($businessId)->active()
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->get()
            ->map(fn($m) => Budget::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByCategory(int $businessId, string $category): array
    {
        return GrowFinanceBudgetModel::forBusiness($businessId)->where('category', $category)->get()
            ->map(fn($m) => Budget::reconstitute($m->toArray()))
            ->toArray();
    }
}
