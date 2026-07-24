<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\RecurringTransaction;
use App\Domain\GrowFinance\Repositories\RecurringTransactionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceRecurringTransactionModel;

class EloquentRecurringTransactionRepository implements RecurringTransactionRepositoryInterface
{
    public function findById(int $id): ?RecurringTransaction
    {
        $model = GrowFinanceRecurringTransactionModel::find($id);
        return $model ? RecurringTransaction::reconstitute($model->toArray()) : null;
    }

    public function save(RecurringTransaction $entity): RecurringTransaction
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceRecurringTransactionModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceRecurringTransactionModel::create($data);
        return RecurringTransaction::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceRecurringTransactionModel::forBusiness($businessId)->get()->map(fn($m) => RecurringTransaction::reconstitute($m->toArray()))->toArray();
    }

    public function findActive(int $businessId): array
    {
        return GrowFinanceRecurringTransactionModel::forBusiness($businessId)->active()->get()->map(fn($m) => RecurringTransaction::reconstitute($m->toArray()))->toArray();
    }

    public function findDueToday(int $businessId): array
    {
        return GrowFinanceRecurringTransactionModel::forBusiness($businessId)->where('is_active', true)->where('next_due_date', '<=', now()->toDateString())->get()->map(fn($m) => RecurringTransaction::reconstitute($m->toArray()))->toArray();
    }

    public function findByType(int $businessId, string $type): array
    {
        return GrowFinanceRecurringTransactionModel::forBusiness($businessId)->where('type', $type)->get()->map(fn($m) => RecurringTransaction::reconstitute($m->toArray()))->toArray();
    }

}
