<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\LifePlus;

use App\Domain\LifePlus\Entities\LifePlusExpense;
use App\Domain\LifePlus\Repositories\ExpenseRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\LifePlusExpenseModel;
use Carbon\Carbon;

class EloquentExpenseRepository implements ExpenseRepositoryInterface
{
    public function findById(int $id): ?LifePlusExpense
    {
        $model = LifePlusExpenseModel::find($id);
        return $model ? LifePlusExpense::reconstitute($model->toArray()) : null;
    }

    public function save(LifePlusExpense $entity): LifePlusExpense
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            LifePlusExpenseModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = LifePlusExpenseModel::create($data);
        return LifePlusExpense::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return LifePlusExpenseModel::where('id', $id)->delete() > 0;
    }

    public function findByUser(int $userId, array $filters = []): array
    {
        $query = LifePlusExpenseModel::where('user_id', $userId);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('expense_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('expense_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            $date = Carbon::parse($filters['month']);
            $query->whereMonth('expense_date', $date->month)
                  ->whereYear('expense_date', $date->year);
        }

        return $query->orderBy('expense_date', 'desc')
            ->limit($filters['limit'] ?? 100)
            ->get()
            ->map(fn($m) => LifePlusExpense::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByLocalId(int $userId, string $localId): ?LifePlusExpense
    {
        $model = LifePlusExpenseModel::where('user_id', $userId)
            ->where('local_id', $localId)
            ->first();
        return $model ? LifePlusExpense::reconstitute($model->toArray()) : null;
    }

    public function getMonthSummary(int $userId, ?string $month = null): array
    {
        $date = $month ? Carbon::parse($month) : now();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $totalSpent = (float) LifePlusExpenseModel::where('user_id', $userId)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        return [
            'month' => $date->format('Y-m'),
            'total_spent' => $totalSpent,
            'transaction_count' => LifePlusExpenseModel::where('user_id', $userId)
                ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
                ->count(),
        ];
    }
}
