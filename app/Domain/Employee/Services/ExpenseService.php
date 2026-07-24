<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Repositories\ExpenseRepositoryInterface;
use Illuminate\Support\Collection;

class ExpenseService
{
    public function __construct(
        private ExpenseRepositoryInterface $expenseRepo
    ) {}

    public function getExpensesForEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        return $this->expenseRepo->findByEmployee($employeeId, $filters);
    }

    public function getExpenseStats(EmployeeId $employeeId, int $year): array
    {
        $expenses = $this->expenseRepo->findForYear($employeeId, $year);

        return [
            'total_submitted' => $expenses->sum('amount'),
            'total_approved' => $expenses->whereIn('status', ['approved', 'reimbursed'])->sum('amount'),
            'total_reimbursed' => $expenses->where('status', 'reimbursed')->sum('amount'),
            'pending_count' => $expenses->whereIn('status', ['draft', 'submitted'])->count(),
            'pending_amount' => $expenses->whereIn('status', ['draft', 'submitted'])->sum('amount'),
            'by_category' => $expenses->groupBy('category')->map(fn($group) => [
                'count' => $group->count(),
                'amount' => $group->sum('amount'),
            ])->toArray(),
        ];
    }

    public function createExpense(EmployeeId $employeeId, array $data): object
    {
        return $this->expenseRepo->create([
            'employee_id' => $employeeId->value(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'ZMW',
            'expense_date' => $data['expense_date'],
            'receipts' => $data['receipts'] ?? [],
            'status' => 'draft',
        ]);
    }

    public function submitExpense(int $expenseId, EmployeeId $employeeId): object
    {
        $expense = $this->expenseRepo->findById($expenseId);

        if (!$expense || $expense->employee_id !== $employeeId->value() || $expense->status !== 'draft') {
            throw new \RuntimeException('Expense not found or cannot be submitted');
        }

        $this->expenseRepo->update($expenseId, [
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return $this->expenseRepo->findById($expenseId);
    }

    public function cancelExpense(int $expenseId, EmployeeId $employeeId): void
    {
        $expense = $this->expenseRepo->findById($expenseId);

        if (!$expense || $expense->employee_id !== $employeeId->value() || !in_array($expense->status, ['draft', 'submitted'])) {
            throw new \RuntimeException('Expense not found or cannot be cancelled');
        }

        $this->expenseRepo->delete($expenseId);
    }

    public function getExpenseCategories(): array
    {
        return [
            'travel' => 'Travel & Transportation',
            'meals' => 'Meals & Entertainment',
            'supplies' => 'Office Supplies',
            'equipment' => 'Equipment',
            'software' => 'Software & Subscriptions',
            'training' => 'Training & Education',
            'communication' => 'Communication',
            'other' => 'Other',
        ];
    }
}