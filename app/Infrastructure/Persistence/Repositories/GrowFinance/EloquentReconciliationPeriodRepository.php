<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\ReconciliationPeriod;
use App\Domain\GrowFinance\Repositories\ReconciliationPeriodRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceReconciliationPeriodModel;

class EloquentReconciliationPeriodRepository implements ReconciliationPeriodRepositoryInterface
{
    public function findById(int $id): ?ReconciliationPeriod
    {
        $model = GrowFinanceReconciliationPeriodModel::find($id);
        return $model ? ReconciliationPeriod::reconstitute($model->toArray()) : null;
    }

    public function save(ReconciliationPeriod $entity): ReconciliationPeriod
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceReconciliationPeriodModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceReconciliationPeriodModel::create($data);
        return ReconciliationPeriod::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceReconciliationPeriodModel::forBusiness($businessId)->get()->map(fn($m) => ReconciliationPeriod::reconstitute($m->toArray()))->toArray();
    }

    public function findByBankAccount(int $bankAccountId): array
    {
        return GrowFinanceReconciliationPeriodModel::where('bank_account_id', $bankAccountId)->get()->map(fn($m) => ReconciliationPeriod::reconstitute($m->toArray()))->toArray();
    }

}
