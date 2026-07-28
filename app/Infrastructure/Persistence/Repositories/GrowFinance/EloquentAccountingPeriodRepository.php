<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\AccountingPeriod;
use App\Domain\GrowFinance\Repositories\AccountingPeriodRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceAccountingPeriodModel;
use DateTimeImmutable;

class EloquentAccountingPeriodRepository implements AccountingPeriodRepositoryInterface
{
    public function findById(int $id): ?AccountingPeriod
    {
        $model = GrowFinanceAccountingPeriodModel::find($id);
        return $model ? AccountingPeriod::reconstitute($model->toArray()) : null;
    }

    public function save(AccountingPeriod $entity): AccountingPeriod
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceAccountingPeriodModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceAccountingPeriodModel::create($data);
        return AccountingPeriod::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceAccountingPeriodModel::forBusiness($businessId)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($m) => AccountingPeriod::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByFiscalYear(int $fiscalYearId): array
    {
        return GrowFinanceAccountingPeriodModel::where('fiscal_year_id', $fiscalYearId)
            ->orderBy('start_date')
            ->get()
            ->map(fn($m) => AccountingPeriod::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findCurrent(int $businessId): ?AccountingPeriod
    {
        $model = GrowFinanceAccountingPeriodModel::forBusiness($businessId)
            ->current()
            ->first();
        return $model ? AccountingPeriod::reconstitute($model->toArray()) : null;
    }

    public function findByStatus(int $businessId, string $status): array
    {
        return GrowFinanceAccountingPeriodModel::forBusiness($businessId)
            ->withStatus($status)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($m) => AccountingPeriod::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByDate(int $businessId, DateTimeImmutable $date): ?AccountingPeriod
    {
        $dateStr = $date->format('Y-m-d');
        $model = GrowFinanceAccountingPeriodModel::forBusiness($businessId)
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->first();
        return $model ? AccountingPeriod::reconstitute($model->toArray()) : null;
    }

    public function findRange(int $businessId, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        return GrowFinanceAccountingPeriodModel::forBusiness($businessId)
            ->where('start_date', '>=', $start->format('Y-m-d'))
            ->where('end_date', '<=', $end->format('Y-m-d'))
            ->orderBy('start_date')
            ->get()
            ->map(fn($m) => AccountingPeriod::reconstitute($m->toArray()))
            ->toArray();
    }
}
