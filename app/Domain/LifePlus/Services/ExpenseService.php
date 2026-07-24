<?php

namespace App\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusExpense;
use App\Domain\LifePlus\Repositories\BudgetRepositoryInterface;
use App\Domain\LifePlus\Repositories\ExpenseRepositoryInterface;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenseRepo,
        private readonly BudgetRepositoryInterface $budgetRepo,
    ) {}

    public function getExpenses(int $userId, array $filters = []): array
    {
        return array_map(fn($e) => $this->mapExpense($e), $this->expenseRepo->findByUser($userId, $filters));
    }

    public function createExpense(int $userId, array $data): array
    {
        $expense = LifePlusExpense::reconstitute([
            'user_id' => $userId,
            'category_id' => $data['category_id'] ?? null,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'expense_date' => $data['expense_date'] ?? now()->toDateString(),
            'is_synced' => $data['is_synced'] ?? true,
            'local_id' => $data['local_id'] ?? null,
        ]);

        return $this->mapExpense($this->expenseRepo->save($expense));
    }

    public function updateExpense(int $id, int $userId, array $data): ?array
    {
        $expense = $this->expenseRepo->findById($id);
        if (!$expense || $expense->userId !== $userId) return null;

        $merged = array_merge($expense->toArray(), $data);
        return $this->mapExpense($this->expenseRepo->save(LifePlusExpense::reconstitute($merged)));
    }

    public function deleteExpense(int $id, int $userId): bool
    {
        $expense = $this->expenseRepo->findById($id);
        if (!$expense || $expense->userId !== $userId) return false;
        return $this->expenseRepo->delete($id);
    }

    public function getMonthSummary(int $userId, ?string $month = null): array
    {
        return $this->expenseRepo->getMonthSummary($userId, $month);
    }

    public function getCategories(int $userId): array
    {
        return []; // Returned from controller via Eloquent directly for simplicity
    }

    public function createCategory(int $userId, array $data): array
    {
        return []; // Handled in controller directly
    }

    public function syncExpenses(int $userId, array $expenses): array
    {
        $synced = [];
        foreach ($expenses as $expense) {
            if (!empty($expense['local_id'])) {
                $existing = $this->expenseRepo->findByLocalId($userId, $expense['local_id']);
                if ($existing) {
                    $synced[] = $this->updateExpense($existing->id, $userId, $expense);
                } else {
                    $synced[] = $this->createExpense($userId, $expense);
                }
            } else {
                $synced[] = $this->createExpense($userId, $expense);
            }
        }
        return array_filter($synced);
    }

    private function mapExpense(LifePlusExpense $expense): array
    {
        return [
            'id' => $expense->id,
            'category_id' => $expense->categoryId,
            'amount' => $expense->amount,
            'description' => $expense->description,
            'expense_date' => $expense->expenseDate->format('Y-m-d'),
            'formatted_date' => $expense->expenseDate->format('M d, Y'),
            'is_synced' => $expense->isSynced,
            'local_id' => $expense->localId,
        ];
    }
}
