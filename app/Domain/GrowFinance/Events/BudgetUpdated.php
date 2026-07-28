<?php

namespace App\Domain\GrowFinance\Events;

class BudgetUpdated
{
    public const NAME = 'growfinance.budget.updated.v1';

    public function __construct(
        public readonly int $budgetId,
        public readonly int $companyId,
        public readonly float $budgetedAmount,
        public readonly float $spentAmount,
        public readonly float $remainingAmount,
        public readonly string $category,
        public readonly string $period,
    ) {}

    public function toPayload(): array
    {
        return [
            'budget_id' => $this->budgetId,
            'company_id' => $this->companyId,
            'budgeted_amount' => $this->budgetedAmount,
            'spent_amount' => $this->spentAmount,
            'remaining_amount' => $this->remainingAmount,
            'category' => $this->category,
            'period' => $this->period,
        ];
    }
}
