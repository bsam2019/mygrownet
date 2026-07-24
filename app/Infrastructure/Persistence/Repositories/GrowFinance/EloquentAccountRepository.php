<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceAccountModel;

class EloquentAccountRepository implements AccountRepositoryInterface
{
    public function findById(int $id): ?Account
    {
        $model = GrowFinanceAccountModel::find($id);
        return $model ? Account::reconstitute($model->toArray()) : null;
    }

    public function save(Account $entity): Account
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceAccountModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceAccountModel::create($data);
        return Account::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceAccountModel::forBusiness($businessId)->get()
            ->map(fn($m) => Account::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActive(int $businessId): array
    {
        return GrowFinanceAccountModel::forBusiness($businessId)->active()->get()
            ->map(fn($m) => Account::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByCode(int $businessId, string $code): ?Account
    {
        $model = GrowFinanceAccountModel::forBusiness($businessId)->where('code', $code)->first();
        return $model ? Account::reconstitute($model->toArray()) : null;
    }

    public function findOfType(int $businessId, string $type): array
    {
        return GrowFinanceAccountModel::forBusiness($businessId)->where('type', $type)->get()
            ->map(fn($m) => Account::reconstitute($m->toArray()))
            ->toArray();
    }
}
