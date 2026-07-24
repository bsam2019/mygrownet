<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\ReconciliationMatch;
use App\Domain\GrowFinance\Repositories\ReconciliationMatchRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceReconciliationMatchModel;

class EloquentReconciliationMatchRepository implements ReconciliationMatchRepositoryInterface
{
    public function findById(int $id): ?ReconciliationMatch
    {
        $model = GrowFinanceReconciliationMatchModel::find($id);
        return $model ? ReconciliationMatch::reconstitute($model->toArray()) : null;
    }

    public function save(ReconciliationMatch $entity): ReconciliationMatch
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceReconciliationMatchModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceReconciliationMatchModel::create($data);
        return ReconciliationMatch::reconstitute($model->toArray());
    }

    public function findByPeriod(int $reconciliationPeriodId): array
    {
        return GrowFinanceReconciliationMatchModel::where('reconciliation_period_id', $reconciliationPeriodId)->get()->map(fn($m) => ReconciliationMatch::reconstitute($m->toArray()))->toArray();
    }

    public function findByStatementLine(int $statementLineId): array
    {
        return GrowFinanceReconciliationMatchModel::where('bank_statement_line_id', $statementLineId)->get()->map(fn($m) => ReconciliationMatch::reconstitute($m->toArray()))->toArray();
    }

}
