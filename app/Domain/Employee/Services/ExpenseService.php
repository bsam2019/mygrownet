<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Models\EmployeeExpense;
use Illuminate\Support\Collection;

class ExpenseService
{
    public function getExpensesForEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        $query = EmployeeExpense::forEmployee($employeeId->value())
            ->with('approver');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (!empty($filters['year'])) {
            $query->forYear((int) $filters['year']);
        }

        return $query->orderBy('expense_date', 'desc')->get();
    }

    public function getExpenseStats(EmployeeId $employeeId, int $year): array
    {
        $expenses = EmployeeExpense::forEmployee($employeeId->value())
            ->forYear($year)
            ->get();

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

    public function createExpense(EmployeeId $employeeId, array $data): EmployeeExpense
    {
        return EmployeeExpense::create([
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

    public function submitExpense(int $expenseId, EmployeeId $employeeId): EmployeeExpense
    {
        $expense = EmployeeExpense::where('id', $expenseId)
            ->where('employee_id', $employeeId->value())
            ->where('status', 'draft')
            ->firstOrFail();

        $expense->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return $expense;
    }

    public function cancelExpense(int $expenseId, EmployeeId $employeeId): void
    {
        $expense = EmployeeExpense::where('id', $expenseId)
            ->where('employee_id', $employeeId->value())
            ->whereIn('status', ['draft', 'submitted'])
            ->firstOrFail();

        $expense->delete();
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
