<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\LifePlus;

use App\Domain\LifePlus\Entities\LifePlusBudget;
use App\Domain\LifePlus\Repositories\BudgetRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\LifePlusBudgetModel;

class EloquentBudgetRepository implements BudgetRepositoryInterface
{
    public function findById(int $id): ?LifePlusBudget
    {
        $model = LifePlusBudgetModel::find($id);
        return $model ? LifePlusBudget::reconstitute($model->toArray()) : null;
    }

    public function save(LifePlusBudget $entity): LifePlusBudget
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            LifePlusBudgetModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = LifePlusBudgetModel::create($data);
        return LifePlusBudget::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return LifePlusBudgetModel::where('id', $id)->delete() > 0;
    }

    public function findByUser(int $userId): array
    {
        return LifePlusBudgetModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => LifePlusBudget::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findCurrent(int $userId): ?LifePlusBudget
    {
        $model = LifePlusBudgetModel::where('user_id', $userId)
            ->whereNull('category_id')
            ->where('period', 'monthly')
            ->where('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->first();

        return $model ? LifePlusBudget::reconstitute($model->toArray()) : null;
    }
}
