<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Expense;
use App\Domain\BMS\Repositories\ExpenseRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\ExpenseModel;

class EloquentExpenseRepository implements ExpenseRepositoryInterface
{
    public function findById(int $id): ?Expense
    {
        $model = ExpenseModel::find($id);
        return $model ? Expense::reconstitute($model->toArray()) : null;
    }

    public function save(Expense $entity): Expense
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            ExpenseModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = ExpenseModel::create($data);
        return Expense::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return ExpenseModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Expense::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByCategory(int $categoryId): array
    {
        return ExpenseModel::where('category_id', $categoryId)->get()
            ->map(fn($m) => Expense::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByJob(int $jobId): array
    {
        return ExpenseModel::where('job_id', $jobId)->get()
            ->map(fn($m) => Expense::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByApprovalStatus(int $companyId, string $status): array
    {
        return ExpenseModel::where('company_id', $companyId)->where('approval_status', $status)->get()
            ->map(fn($m) => Expense::reconstitute($m->toArray()))
            ->toArray();
    }

    public function getSummary(int $companyId): array
    {
        return [
            'total_expenses' => ExpenseModel::where('company_id', $companyId)
                ->where('approval_status', 'approved')
                ->sum('amount'),
            'pending_approval' => ExpenseModel::where('company_id', $companyId)
                ->where('approval_status', 'pending')
                ->count(),
            'this_month' => ExpenseModel::where('company_id', $companyId)
                ->where('approval_status', 'approved')
                ->whereMonth('expense_date', now()->month)
                ->sum('amount'),
        ];
    }
}
