<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\IntercompanyTransaction;
use App\Domain\GrowFinance\Repositories\IntercompanyTransactionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceIntercompanyTransactionModel;

class EloquentIntercompanyTransactionRepository implements IntercompanyTransactionRepositoryInterface
{
    public function findById(int $id): ?IntercompanyTransaction
    {
        $model = GrowFinanceIntercompanyTransactionModel::find($id);
        return $model ? IntercompanyTransaction::reconstitute($model->toArray()) : null;
    }

    public function findByOrg(int $orgId): array
    {
        return GrowFinanceIntercompanyTransactionModel::where('from_org_id', $orgId)
            ->orWhere('to_org_id', $orgId)
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(fn($m) => IntercompanyTransaction::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByFromOrg(int $fromOrgId): array
    {
        return GrowFinanceIntercompanyTransactionModel::fromOrg($fromOrgId)
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(fn($m) => IntercompanyTransaction::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByToOrg(int $toOrgId): array
    {
        return GrowFinanceIntercompanyTransactionModel::toOrg($toOrgId)
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(fn($m) => IntercompanyTransaction::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findPending(): array
    {
        return GrowFinanceIntercompanyTransactionModel::pending()
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(fn($m) => IntercompanyTransaction::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findUnmatched(): array
    {
        return GrowFinanceIntercompanyTransactionModel::whereNull('matched_transaction_id')
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(fn($m) => IntercompanyTransaction::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findMatched(): array
    {
        return GrowFinanceIntercompanyTransactionModel::matched()
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(fn($m) => IntercompanyTransaction::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByStatus(string $status): array
    {
        return GrowFinanceIntercompanyTransactionModel::withStatus($status)
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(fn($m) => IntercompanyTransaction::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(IntercompanyTransaction $tx): IntercompanyTransaction
    {
        $data = $tx->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceIntercompanyTransactionModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceIntercompanyTransactionModel::create($data);
        return IntercompanyTransaction::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        GrowFinanceIntercompanyTransactionModel::where('id', $id)->delete();
    }
}
