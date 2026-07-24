<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\BankStatement;
use App\Domain\GrowFinance\Repositories\BankStatementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceBankStatementModel;

class EloquentBankStatementRepository implements BankStatementRepositoryInterface
{
    public function findById(int $id): ?BankStatement
    {
        $model = GrowFinanceBankStatementModel::find($id);
        return $model ? BankStatement::reconstitute($model->toArray()) : null;
    }

    public function save(BankStatement $entity): BankStatement
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceBankStatementModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceBankStatementModel::create($data);
        return BankStatement::reconstitute($model->toArray());
    }

    public function findByBankAccount(int $bankAccountId): array
    {
        return GrowFinanceBankStatementModel::where('bank_account_id', $bankAccountId)->get()
            ->map(fn($m) => BankStatement::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceBankStatementModel::whereHas('bankAccount', function ($q) use ($businessId) {
            $q->where('business_id', $businessId);
        })->get()
            ->map(fn($m) => BankStatement::reconstitute($m->toArray()))
            ->toArray();
    }
}
