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

    public function delete(int $id): bool
    {
        return (bool) GrowFinanceAccountModel::where('id', $id)->delete();
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

    public function getChart(int $businessId): array
    {
        $models = GrowFinanceAccountModel::forBusiness($businessId)
            ->orderBy('code')
            ->get();

        $accounts = $models->map(fn($m) => Account::reconstitute($m->toArray()))->keyBy('id');

        foreach ($accounts as $account) {
            if ($account->parentId && isset($accounts[$account->parentId])) {
                $parent = $accounts[$account->parentId];
                $children = $parent->children() ?? [];
                $children[] = $account;
                $parent->setChildren($children);
                $account->setParentAccount($parent);
            }
        }

        return $accounts->values()->toArray();
    }

    public function getChildren(int $parentId): array
    {
        return GrowFinanceAccountModel::where('parent_id', $parentId)->get()
            ->map(fn($m) => Account::reconstitute($m->toArray()))
            ->toArray();
    }

    public function getParent(int $accountId): ?Account
    {
        $model = GrowFinanceAccountModel::find($accountId);
        if (!$model || !$model->parent_id) {
            return null;
        }
        return $this->findById($model->parent_id);
    }

    public function getAccountsByStatementCategory(int $businessId, string $statementCategory): array
    {
        return GrowFinanceAccountModel::forBusiness($businessId)
            ->statementCategory($statementCategory)
            ->orderBy('code')
            ->get()
            ->map(fn($m) => Account::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByCodes(int $businessId, array $codes): array
    {
        return GrowFinanceAccountModel::forBusiness($businessId)
            ->whereIn('code', $codes)
            ->orderBy('code')
            ->get()
            ->map(fn($m) => Account::reconstitute($m->toArray()))
            ->toArray();
    }
}
