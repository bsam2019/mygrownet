<?php

namespace App\Domain\GrowFinance\Services;

use App\Infrastructure\Persistence\Eloquent\GrowFinanceBudgetModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceExpenseModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    /**
     * Get all budgets for a business
     */
    public function getForBusiness(User $user, bool $activeOnly = true): \Illuminate\Database\Eloquent\Collection
    {
        $query = GrowFinanceBudgetModel::forBusiness($user->id)
            ->with('account');

        if ($activeOnly) {
            $query->active();
        }

        return $query->orderBy('end_date')->get();
    }

    /**
     * Get current period budgets
     */
    public function getCurrentBudgets(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return GrowFinanceBudgetModel::forBusiness($user->id)
            ->active()
            ->current()
            ->with('account')
            ->get();
    }

    /**
     * Create a new budget
     */
    public function create(User $user, array $data): GrowFinanceBudgetModel
    {
        $dates = $this->calculatePeriodDates($data['period'], $data['start_date'] ?? null);

        $budget = GrowFinanceBudgetModel::create([
            'business_id' => $user->id,
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'account_id' => $data['account_id'] ?? null,
            'period' => $data['period'],
            'start_date' => $dates['start'],
            'end_date' => $data['end_date'] ?? $dates['end'],
            'budgeted_amount' => $data['budgeted_amount'],
            'alert_threshold' => $data['alert_threshold'] ?? 80,
            'rollover_unused' => $data['rollover_unused'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);

        // Calculate initial spent amount
        $this->recalculateSpent($budget);

        return $budget->fresh();
    }

    /**
     * Recalculate spent amount for a budget
     */
    public function recalculateSpent(GrowFinanceBudgetModel $budget): void
    {
        $query = GrowFinanceExpenseModel::forBusiness($budget->business_id)
            ->whereBetween('expense_date', [$budget->start_date, $budget->end_date]);

        // Filter by category if set
        if ($budget->category) {
            $query->where('category', $budget->category);
        }

        // Filter by account if set
        if ($budget->account_id) {
            $query->where('account_id', $budget->account_id);
        }

        $spent = $query->sum('amount');

        $budget->update(['spent_amount' => $spent]);
    }

    /**
     * Recalculate all active budgets for a user
     */
    public function recalculateAllBudgets(User $user): void
    {
        $budgets = GrowFinanceBudgetModel::forBusiness($user->id)
            ->active()
            ->current()
            ->get();

        foreach ($budgets as $budget) {
            $this->recalculateSpent($budget);
        }
    }

    /**
     * Get budget summary for dashboard
     */
    public function getSummary(User $user): array
    {
        $budgets = $this->getCurrentBudgets($user);

        $totalBudgeted = $budgets->sum('budgeted_amount');
        $totalSpent = $budgets->sum('spent_amount');
        $overBudget = $budgets->filter(fn($b) => $b->isOverBudget())->count();
        $nearLimit = $budgets->filter(fn($b) => $b->isNearLimit())->count();

        return [
            'total_budgets' => $budgets->count(),
            'total_budgeted' => $totalBudgeted,
            'total_spent' => $totalSpent,
            'total_remaining' => max(0, $totalBudgeted - $totalSpent),
            'overall_percentage' => $totalBudgeted > 0 
                ? round(($totalSpent / $totalBudgeted) * 100, 1) 
                : 0,
            'over_budget_count' => $overBudget,
            'near_limit_count' => $nearLimit,
            'on_track_count' => $budgets->count() - $overBudget - $nearLimit,
        ];
    }

    /**
     * Get budgets that need attention (over or near limit)
     */
    public function getBudgetsNeedingAttention(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return GrowFinanceBudgetModel::forBusiness($user->id)
            ->active()
            ->current()
            ->get()
            ->filter(fn($b) => $b->isOverBudget() || $b->isNearLimit());
    }

    /**
     * Calculate period dates based on period type
     */
    private function calculatePeriodDates(string $period, ?string $startDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate) : now();

        return match ($period) {
            'monthly' => [
                'start' => $start->copy()->startOfMonth(),
                'end' => $start->copy()->endOfMonth(),
            ],
            'quarterly' => [
                'start' => $start->copy()->firstOfQuarter(),
                'end' => $start->copy()->lastOfQuarter(),
            ],
            'yearly' => [
                'start' => $start->copy()->startOfYear(),
                'end' => $start->copy()->endOfYear(),
            ],
            default => [
                'start' => $start,
                'end' => $start->copy()->addMonth(),
            ],
        };
    }

    /**
     * Create next period budget from existing one
     */
    public function rolloverBudget(GrowFinanceBudgetModel $budget): GrowFinanceBudgetModel
    {
        $nextStart = $budget->end_date->copy()->addDay();
        $dates = $this->calculatePeriodDates($budget->period, $nextStart->toDateString());

        $carryover = 0;
        if ($budget->rollover_unused && $budget->remaining_amount > 0) {
            $carryover = $budget->remaining_amount;
        }

        return GrowFinanceBudgetModel::create([
            'business_id' => $budget->business_id,
            'name' => $budget->name,
            'category' => $budget->category,
            'account_id' => $budget->account_id,
            'period' => $budget->period,
            'start_date' => $dates['start'],
            'end_date' => $dates['end'],
            'budgeted_amount' => $budget->budgeted_amount + $carryover,
            'alert_threshold' => $budget->alert_threshold,
            'rollover_unused' => $budget->rollover_unused,
            'notes' => $budget->notes,
        ]);
    }
}
