<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusBudgetModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusSavingsGoalModel;
use Carbon\Carbon;

class BudgetService
{
    // Budgets
    public function getBudgets(int $userId): array
    {
        return LifePlusBudgetModel::where('user_id', $userId)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($b) => $this->mapBudget($b))
            ->toArray();
    }

    public function getCurrentBudget(int $userId): ?array
    {
        $budget = LifePlusBudgetModel::where('user_id', $userId)
            ->whereNull('category_id')
            ->where('period', 'monthly')
            ->where('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->first();

        return $budget ? $this->mapBudget($budget) : null;
    }

    public function createBudget(int $userId, array $data): array
    {
        $budget = LifePlusBudgetModel::create([
            'user_id' => $userId,
            'category_id' => $data['category_id'] ?? null,
            'amount' => $data['amount'],
            'period' => $data['period'] ?? 'monthly',
            'start_date' => $data['start_date'] ?? now()->startOfMonth()->toDateString(),
            'end_date' => $data['end_date'] ?? null,
        ]);

        return $this->mapBudget($budget->load('category'));
    }

    public function updateBudget(int $id, int $userId, array $data): ?array
    {
        $budget = LifePlusBudgetModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$budget) return null;

        $budget->update($data);
        return $this->mapBudget($budget->fresh('category'));
    }

    public function deleteBudget(int $id, int $userId): bool
    {
        return LifePlusBudgetModel::where('id', $id)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    // Savings Goals
    public function getSavingsGoals(int $userId): array
    {
        return LifePlusSavingsGoalModel::where('user_id', $userId)
            ->orderBy('status')
            ->orderBy('target_date')
            ->get()
            ->map(fn($g) => $this->mapSavingsGoal($g))
            ->toArray();
    }

    public function createSavingsGoal(int $userId, array $data): array
    {
        $goal = LifePlusSavingsGoalModel::create([
            'user_id' => $userId,
            'name' => $data['name'],
            'target_amount' => $data['target_amount'],
            'current_amount' => $data['current_amount'] ?? 0,
            'target_date' => $data['target_date'] ?? null,
            'status' => 'active',
        ]);

        return $this->mapSavingsGoal($goal);
    }

    public function updateSavingsGoal(int $id, int $userId, array $data): ?array
    {
        $goal = LifePlusSavingsGoalModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$goal) return null;

        $goal->update($data);

        // Auto-complete if target reached
        if ($goal->current_amount >= $goal->target_amount && $goal->status === 'active') {
            $goal->update(['status' => 'completed']);
        }

        return $this->mapSavingsGoal($goal->fresh());
    }

    public function contributeSavings(int $id, int $userId, float $amount): ?array
    {
        $goal = LifePlusSavingsGoalModel::where('id', $id)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (!$goal) return null;

        $goal->increment('current_amount', $amount);

        if ($goal->current_amount >= $goal->target_amount) {
            $goal->update(['status' => 'completed']);
        }

        return $this->mapSavingsGoal($goal->fresh());
    }

    public function deleteSavingsGoal(int $id, int $userId): bool
    {
        return LifePlusSavingsGoalModel::where('id', $id)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    private function mapBudget($budget): array
    {
        return [
            'id' => $budget->id,
            'category_id' => $budget->category_id,
            'category_name' => $budget->category?->name ?? 'Total Budget',
            'amount' => (float) $budget->amount,
            'period' => $budget->period,
            'start_date' => $budget->start_date->format('Y-m-d'),
            'end_date' => $budget->end_date?->format('Y-m-d'),
        ];
    }

    private function mapSavingsGoal($goal): array
    {
        $progress = $goal->target_amount > 0 
            ? min(100, ($goal->current_amount / $goal->target_amount) * 100) 
            : 0;

        return [
            'id' => $goal->id,
            'name' => $goal->name,
            'target_amount' => (float) $goal->target_amount,
            'current_amount' => (float) $goal->current_amount,
            'remaining' => max(0, (float) ($goal->target_amount - $goal->current_amount)),
            'progress' => round($progress, 1),
            'target_date' => $goal->target_date?->format('Y-m-d'),
            'formatted_target_date' => $goal->target_date?->format('M d, Y'),
            'status' => $goal->status,
            'is_completed' => $goal->status === 'completed',
        ];
    }
}
