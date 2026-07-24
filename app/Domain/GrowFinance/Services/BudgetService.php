<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\Budget;
use App\Domain\GrowFinance\Repositories\BudgetRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    public function __construct(
        private BudgetRepositoryInterface $budgetRepo,
        private ExpenseRepositoryInterface $expenseRepo,
    ) {}

    /**
     * Get all budgets for a business
     */
    public function getForBusiness(int $businessId, bool $activeOnly = true): array
    {
        $budgets = $activeOnly
            ? $this->budgetRepo->findActive($businessId)
            : $this->budgetRepo->findByBusiness($businessId);

        usort($budgets, fn(Budget $a, Budget $b) => ($a->endDate?->getTimestamp() ?? 0) <=> ($b->endDate?->getTimestamp() ?? 0));

        return array_map(fn(Budget $b) => $b->toArray(), $budgets);
    }

    /**
     * Get current period budgets
     */
    public function getCurrentBudgets(int $businessId): array
    {
        return array_map(
            fn(Budget $b) => $b->toArray(),
            $this->budgetRepo->findCurrent($businessId)
        );
    }

    /**
     * Create a new budget
     */
    public function create(int $businessId, array $data): array
    {
        $dates = $this->calculatePeriodDates($data['period'], $data['start_date'] ?? null);

        $budget = $this->budgetRepo->save(new Budget(
            id: null,
            businessId: $businessId,
            name: $data['name'],
            category: $data['category'] ?? null,
            accountId: $data['account_id'] ?? null,
            period: $data['period'],
            startDate: \DateTimeImmutable::createFromMutable($dates['start']),
            endDate: isset($data['end_date'])
                ? new \DateTimeImmutable($data['end_date'])
                : \DateTimeImmutable::createFromMutable($dates['end']),
            budgetedAmount: (float) $data['budgeted_amount'],
            alertThreshold: (float) ($data['alert_threshold'] ?? 80),
            rolloverUnused: (bool) ($data['rollover_unused'] ?? false),
            notes: $data['notes'] ?? null,
        ));

        $budget = $this->recalculateSpent($budget->id);

        return $budget->toArray();
    }

    /**
     * Recalculate spent amount for a budget
     */
    public function recalculateSpent(int $budgetId): Budget
    {
        $budget = $this->budgetRepo->findById($budgetId);
        if (!$budget) {
            throw new \RuntimeException('Budget not found');
        }

        $query = DB::table('growfinance_expenses')
            ->where('business_id', $budget->businessId)
            ->whereBetween('expense_date', [
                $budget->startDate?->format('Y-m-d'),
                $budget->endDate?->format('Y-m-d'),
            ]);

        if ($budget->category) {
            $query->where('category', $budget->category);
        }

        if ($budget->accountId) {
            $query->where('account_id', $budget->accountId);
        }

        $spent = (float) $query->sum('amount');

        return $this->budgetRepo->save(new Budget(
            id: $budget->id,
            businessId: $budget->businessId,
            name: $budget->name,
            category: $budget->category,
            accountId: $budget->accountId,
            period: $budget->period,
            startDate: $budget->startDate,
            endDate: $budget->endDate,
            budgetedAmount: $budget->budgetedAmount,
            spentAmount: $spent,
            isActive: $budget->isActive,
            rolloverUnused: $budget->rolloverUnused,
            alertThreshold: $budget->alertThreshold,
            notes: $budget->notes,
            createdAt: $budget->createdAt,
            updatedAt: null,
        ));
    }

    /**
     * Recalculate all active budgets for a user
     */
    public function recalculateAllBudgets(int $businessId): void
    {
        $budgets = $this->budgetRepo->findCurrent($businessId);

        foreach ($budgets as $budget) {
            $this->recalculateSpent($budget);
        }
    }

    /**
     * Get budget summary for dashboard
     */
    public function getSummary(int $businessId): array
    {
        $budgetArrays = $this->getCurrentBudgets($businessId);

        $budgets = array_map(fn(array $b) => Budget::reconstitute($b), $budgetArrays);

        $totalBudgeted = array_sum(array_map(fn(Budget $b) => $b->budgetedAmount, $budgets));
        $totalSpent = array_sum(array_map(fn(Budget $b) => $b->spentAmount, $budgets));
        $overBudget = count(array_filter($budgets, fn(Budget $b) => $b->isOverBudget()));
        $nearLimit = count(array_filter($budgets, fn(Budget $b) => $b->isNearLimit()));

        return [
            'total_budgets' => count($budgets),
            'total_budgeted' => $totalBudgeted,
            'total_spent' => $totalSpent,
            'total_remaining' => max(0, $totalBudgeted - $totalSpent),
            'overall_percentage' => $totalBudgeted > 0
                ? round(($totalSpent / $totalBudgeted) * 100, 1)
                : 0,
            'over_budget_count' => $overBudget,
            'near_limit_count' => $nearLimit,
            'on_track_count' => count($budgets) - $overBudget - $nearLimit,
        ];
    }

    /**
     * Get budgets that need attention (over or near limit)
     */
    public function getBudgetsNeedingAttention(int $businessId): array
    {
        $budgets = $this->budgetRepo->findCurrent($businessId);

        $filtered = array_filter(
            $budgets,
            fn(Budget $b) => $b->isOverBudget() || $b->isNearLimit()
        );

        return array_map(fn(Budget $b) => $b->toArray(), $filtered);
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
    public function rolloverBudget(int $budgetId): array
    {
        $budget = $this->budgetRepo->findById($budgetId);
        if (!$budget) {
            throw new \RuntimeException('Budget not found');
        }

        $nextStart = $budget->startDate?->add(new \DateInterval('P1D'));
        if (!$nextStart) {
            throw new \RuntimeException('Budget has no start date');
        }

        $dates = $this->calculatePeriodDates($budget->period ?? 'monthly', $nextStart->format('Y-m-d'));

        $carryover = 0.0;
        if ($budget->rolloverUnused && $budget->getRemainingAmount() > 0) {
            $carryover = $budget->getRemainingAmount();
        }

        $newBudget = $this->budgetRepo->save(new Budget(
            id: null,
            businessId: $budget->businessId,
            name: $budget->name,
            category: $budget->category,
            accountId: $budget->accountId,
            period: $budget->period,
            startDate: \DateTimeImmutable::createFromMutable($dates['start']),
            endDate: \DateTimeImmutable::createFromMutable($dates['end']),
            budgetedAmount: $budget->budgetedAmount + $carryover,
            alertThreshold: $budget->alertThreshold,
            rolloverUnused: $budget->rolloverUnused,
            notes: $budget->notes,
        ));

        return $newBudget->toArray();
    }
}
