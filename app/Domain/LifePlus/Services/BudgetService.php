<?php

namespace App\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusBudget;
use App\Domain\LifePlus\Repositories\BudgetRepositoryInterface;

class BudgetService
{
    public function __construct(
        private readonly BudgetRepositoryInterface $budgetRepo,
    ) {}

    public function getBudgets(int $userId): array
    {
        return array_map(fn($b) => $this->mapBudget($b), $this->budgetRepo->findByUser($userId));
    }

    public function getCurrentBudget(int $userId): ?array
    {
        $budget = $this->budgetRepo->findCurrent($userId);
        return $budget ? $this->mapBudget($budget) : null;
    }

    public function createBudget(int $userId, array $data): array
    {
        $budget = LifePlusBudget::reconstitute([
            'user_id' => $userId,
            'category_id' => $data['category_id'] ?? null,
            'amount' => $data['amount'],
            'period' => $data['period'] ?? 'monthly',
            'start_date' => $data['start_date'] ?? now()->startOfMonth()->toDateString(),
            'end_date' => $data['end_date'] ?? null,
        ]);

        return $this->mapBudget($this->budgetRepo->save($budget));
    }

    public function updateBudget(int $id, int $userId, array $data): ?array
    {
        $budget = $this->budgetRepo->findById($id);
        if (!$budget || $budget->userId !== $userId) return null;

        $merged = array_merge($budget->toArray(), $data);
        return $this->mapBudget($this->budgetRepo->save(LifePlusBudget::reconstitute($merged)));
    }

    public function deleteBudget(int $id, int $userId): bool
    {
        $budget = $this->budgetRepo->findById($id);
        if (!$budget || $budget->userId !== $userId) return false;
        return $this->budgetRepo->delete($id);
    }

    private function mapBudget(LifePlusBudget $budget): array
    {
        return [
            'id' => $budget->id,
            'category_id' => $budget->categoryId,
            'amount' => $budget->amount,
            'period' => $budget->period,
            'start_date' => $budget->startDate->format('Y-m-d'),
            'end_date' => $budget->endDate?->format('Y-m-d'),
        ];
    }
}
