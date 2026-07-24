<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\BankStatementLine;
use App\Domain\GrowFinance\Repositories\BankStatementLineRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceBankStatementLineModel;

class EloquentBankStatementLineRepository implements BankStatementLineRepositoryInterface
{
    public function findById(int $id): ?BankStatementLine
    {
        $model = GrowFinanceBankStatementLineModel::find($id);
        return $model ? BankStatementLine::reconstitute($model->toArray()) : null;
    }

    public function save(BankStatementLine $entity): BankStatementLine
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceBankStatementLineModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceBankStatementLineModel::create($data);
        return BankStatementLine::reconstitute($model->toArray());
    }

    public function findByStatement(int $statementId): array
    {
        return GrowFinanceBankStatementLineModel::where('bank_statement_id', $statementId)->get()
            ->map(fn($m) => BankStatementLine::reconstitute($m->toArray()))
            ->toArray();
    }
}
