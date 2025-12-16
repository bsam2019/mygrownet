<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusExpenseModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusExpenseCategoryModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusBudgetModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExpenseService
{
    public function getExpenses(int $userId, array $filters = []): array
    {
        $query = LifePlusExpenseModel::where('user_id', $userId)
            ->with('category');

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
            ->orderBy('created_at', 'desc')
            ->limit($filters['limit'] ?? 100)
            ->get()
            ->map(fn($e) => $this->mapExpense($e))
            ->toArray();
    }

    public function createExpense(int $userId, array $data): array
    {
        $expense = LifePlusExpenseModel::create([
            'user_id' => $userId,
            'category_id' => $data['category_id'] ?? null,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'expense_date' => $data['expense_date'] ?? now()->toDateString(),
            'is_synced' => $data['is_synced'] ?? true,
            'local_id' => $data['local_id'] ?? null,
        ]);

        return $this->mapExpense($expense->load('category'));
    }

    public function updateExpense(int $id, int $userId, array $data): ?array
    {
        $expense = LifePlusExpenseModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$expense) return null;

        $expense->update($data);
        return $this->mapExpense($expense->fresh('category'));
    }

    public function deleteExpense(int $id, int $userId): bool
    {
        return LifePlusExpenseModel::where('id', $id)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    public function getMonthSummary(int $userId, ?string $month = null): array
    {
        $date = $month ? Carbon::parse($month) : now();
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $expenses = LifePlusExpenseModel::where('user_id', $userId)
            ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->with('category')
            ->get();

        $totalSpent = $expenses->sum('amount');

        // Get budget for this month
        $budget = LifePlusBudgetModel::where('user_id', $userId)
            ->whereNull('category_id')
            ->where('period', 'monthly')
            ->where('start_date', '<=', $endOfMonth)
            ->where(function ($q) use ($startOfMonth) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $startOfMonth);
            })
            ->first();

        $budgetAmount = $budget ? (float) $budget->amount : 0;
        $remaining = $budgetAmount - $totalSpent;
        $percentage = $budgetAmount > 0 ? min(100, ($totalSpent / $budgetAmount) * 100) : 0;

        // Group by category
        $byCategory = $expenses->groupBy('category_id')->map(function ($items, $categoryId) {
            $category = $items->first()->category;
            return [
                'category_id' => $categoryId,
                'category_name' => $category?->name ?? 'Uncategorized',
                'icon' => $category?->icon ?? '💰',
                'color' => $category?->color ?? '#6b7280',
                'total' => (float) $items->sum('amount'),
                'count' => $items->count(),
            ];
        })->values()->sortByDesc('total')->values()->toArray();

        return [
            'month' => $date->format('Y-m'),
            'month_name' => $date->format('F Y'),
            'total_spent' => (float) $totalSpent,
            'budget' => $budgetAmount,
            'remaining' => $remaining,
            'percentage' => round($percentage, 1),
            'is_over_budget' => $remaining < 0,
            'by_category' => $byCategory,
            'transaction_count' => $expenses->count(),
        ];
    }

    // Categories
    public function getCategories(int $userId): array
    {
        $categories = LifePlusExpenseCategoryModel::where(function ($q) use ($userId) {
            $q->whereNull('user_id')
              ->orWhere('user_id', $userId);
        })
        ->orderBy('is_default', 'desc')
        ->orderBy('name')
        ->get();

        return $categories->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'icon' => $c->icon,
            'color' => $c->color,
            'is_default' => $c->is_default,
            'is_custom' => $c->user_id !== null,
        ])->toArray();
    }

    public function createCategory(int $userId, array $data): array
    {
        $category = LifePlusExpenseCategoryModel::create([
            'user_id' => $userId,
            'name' => $data['name'],
            'icon' => $data['icon'] ?? '💰',
            'color' => $data['color'] ?? '#3b82f6',
            'is_default' => false,
        ]);

        return [
            'id' => $category->id,
            'name' => $category->name,
            'icon' => $category->icon,
            'color' => $category->color,
        ];
    }

    public function syncExpenses(int $userId, array $expenses): array
    {
        $synced = [];
        foreach ($expenses as $expense) {
            if (!empty($expense['local_id'])) {
                $existing = LifePlusExpenseModel::where('user_id', $userId)
                    ->where('local_id', $expense['local_id'])
                    ->first();

                if ($existing) {
                    $existing->update($expense);
                    $synced[] = $this->mapExpense($existing->fresh('category'));
                } else {
                    $synced[] = $this->createExpense($userId, $expense);
                }
            } else {
                $synced[] = $this->createExpense($userId, $expense);
            }
        }
        return $synced;
    }

    private function mapExpense($expense): array
    {
        return [
            'id' => $expense->id,
            'category_id' => $expense->category_id,
            'category_name' => $expense->category?->name ?? 'Uncategorized',
            'category_icon' => $expense->category?->icon ?? '💰',
            'category_color' => $expense->category?->color ?? '#6b7280',
            'amount' => (float) $expense->amount,
            'description' => $expense->description,
            'expense_date' => $expense->expense_date->format('Y-m-d'),
            'formatted_date' => $expense->expense_date->format('M d, Y'),
            'is_synced' => $expense->is_synced,
            'local_id' => $expense->local_id,
            'created_at' => $expense->created_at->toISOString(),
        ];
    }
}
