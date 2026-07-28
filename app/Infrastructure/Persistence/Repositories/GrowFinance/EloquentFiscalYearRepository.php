<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\FiscalYear;
use App\Domain\GrowFinance\Repositories\FiscalYearRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceFiscalYearModel;
use DateTimeImmutable;

class EloquentFiscalYearRepository implements FiscalYearRepositoryInterface
{
    public function findById(int $id): ?FiscalYear
    {
        $model = GrowFinanceFiscalYearModel::find($id);
        return $model ? FiscalYear::reconstitute($model->toArray()) : null;
    }

    public function save(FiscalYear $entity): FiscalYear
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceFiscalYearModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceFiscalYearModel::create($data);
        return FiscalYear::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceFiscalYearModel::forBusiness($businessId)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($m) => FiscalYear::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findCurrentForBusiness(int $businessId): ?FiscalYear
    {
        $model = GrowFinanceFiscalYearModel::forBusiness($businessId)
            ->current()
            ->first();
        return $model ? FiscalYear::reconstitute($model->toArray()) : null;
    }

    public function findByDate(int $businessId, DateTimeImmutable $date): ?FiscalYear
    {
        $dateStr = $date->format('Y-m-d');
        $model = GrowFinanceFiscalYearModel::forBusiness($businessId)
            ->where('start_date', '<=', $dateStr)
            ->where('end_date', '>=', $dateStr)
            ->first();
        return $model ? FiscalYear::reconstitute($model->toArray()) : null;
    }
}
