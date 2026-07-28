<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\TaxReturn;
use App\Domain\GrowFinance\Repositories\TaxReturnRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceTaxReturnModel;

class EloquentTaxReturnRepository implements TaxReturnRepositoryInterface
{
    public function findById(int $id): ?TaxReturn
    {
        $model = GrowFinanceTaxReturnModel::find($id);
        return $model ? TaxReturn::reconstitute($model->toArray()) : null;
    }

    public function save(TaxReturn $taxReturn): TaxReturn
    {
        $data = $taxReturn->toArray();
        if ($taxReturn->id) {
            GrowFinanceTaxReturnModel::where('id', $taxReturn->id)->update($data);
            $model = GrowFinanceTaxReturnModel::find($taxReturn->id);
        } else {
            $model = GrowFinanceTaxReturnModel::create($data);
        }
        return TaxReturn::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceTaxReturnModel::forBusiness($businessId)
            ->orderBy('period_start', 'desc')
            ->get()
            ->map(fn($m) => TaxReturn::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByTypeAndPeriod(int $businessId, string $returnType, string $periodStart, string $periodEnd): ?TaxReturn
    {
        $model = GrowFinanceTaxReturnModel::forBusiness($businessId)
            ->byType($returnType)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd)
            ->first();
        return $model ? TaxReturn::reconstitute($model->toArray()) : null;
    }

    public function findByType(int $businessId, string $returnType): array
    {
        return GrowFinanceTaxReturnModel::forBusiness($businessId)
            ->byType($returnType)
            ->orderBy('period_start', 'desc')
            ->get()
            ->map(fn($m) => TaxReturn::reconstitute($m->toArray()))
            ->toArray();
    }

    public function delete(TaxReturn $taxReturn): void
    {
        GrowFinanceTaxReturnModel::where('id', $taxReturn->id)->delete();
    }
}
