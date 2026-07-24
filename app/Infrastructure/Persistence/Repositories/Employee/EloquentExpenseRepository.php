<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Employee;

use App\Domain\Employee\Repositories\ExpenseRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Models\Employee\EmployeeExpense;
use Illuminate\Support\Collection;

class EloquentExpenseRepository implements ExpenseRepositoryInterface
{
    public function findById(int $id): ?EmployeeExpense
    {
        return EmployeeExpense::find($id);
    }

    public function findByEmployee(EmployeeId $employeeId, array $filters = []): Collection
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

    public function findForYear(EmployeeId $employeeId, int $year): Collection
    {
        return EmployeeExpense::forEmployee($employeeId->value())
            ->forYear($year)
            ->get();
    }

    public function create(array $data): EmployeeExpense
    {
        return EmployeeExpense::create($data);
    }

    public function update(int $id, array $data): ?EmployeeExpense
    {
        $expense = EmployeeExpense::find($id);
        if (!$expense) {
            return null;
        }
        $expense->update($data);
        return $expense;
    }

    public function delete(int $id): bool
    {
        return EmployeeExpense::destroy($id) > 0;
    }
}