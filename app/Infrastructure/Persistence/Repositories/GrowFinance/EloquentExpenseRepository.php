<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\Expense;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceExpenseModel;

class EloquentExpenseRepository implements ExpenseRepositoryInterface
{
    public function findById(int $id): ?Expense
    {
        $model = GrowFinanceExpenseModel::find($id);
        return $model ? Expense::reconstitute($model->toArray()) : null;
    }

    public function save(Expense $entity): Expense
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceExpenseModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceExpenseModel::create($data);
        return Expense::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceExpenseModel::forBusiness($businessId)->get()->map(fn($m) => Expense::reconstitute($m->toArray()))->toArray();
    }

    public function findByVendor(int $vendorId): array
    {
        return GrowFinanceExpenseModel::where('vendor_id', $vendorId)->get()->map(fn($m) => Expense::reconstitute($m->toArray()))->toArray();
    }

    public function findByAccount(int $accountId): array
    {
        return GrowFinanceExpenseModel::where('account_id', $accountId)->get()->map(fn($m) => Expense::reconstitute($m->toArray()))->toArray();
    }

    public function findByCategory(int $businessId, string $category): array
    {
        return GrowFinanceExpenseModel::forBusiness($businessId)->where('category', $category)->get()->map(fn($m) => Expense::reconstitute($m->toArray()))->toArray();
    }

    public function findInDateRange(int $businessId, string $startDate, string $endDate): array
    {
        return GrowFinanceExpenseModel::forBusiness($businessId)->whereBetween('expense_date', [$startDate, $endDate])->get()->map(fn($m) => Expense::reconstitute($m->toArray()))->toArray();
    }

}
