<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\BankAccount;
use App\Domain\GrowFinance\Repositories\BankAccountRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceBankAccountModel;

class EloquentBankAccountRepository implements BankAccountRepositoryInterface
{
    public function findById(int $id): ?BankAccount
    {
        $model = GrowFinanceBankAccountModel::find($id);
        return $model ? BankAccount::reconstitute($model->toArray()) : null;
    }

    public function save(BankAccount $entity): BankAccount
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);

        if ($id) {
            GrowFinanceBankAccountModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceBankAccountModel::create($data);
        return BankAccount::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceBankAccountModel::forBusiness($businessId)->get()
            ->map(fn($m) => BankAccount::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $businessId): array
    {
        return GrowFinanceBankAccountModel::forBusiness($businessId)->active()->get()
            ->map(fn($m) => BankAccount::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findDefault(int $businessId): ?BankAccount
    {
        $model = GrowFinanceBankAccountModel::forBusiness($businessId)->where('is_default', true)->first();
        return $model ? BankAccount::reconstitute($model->toArray()) : null;
    }
}
