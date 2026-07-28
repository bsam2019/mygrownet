<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\TaxRate;
use App\Domain\GrowFinance\Repositories\TaxRateRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceTaxRateModel;

class EloquentTaxRateRepository implements TaxRateRepositoryInterface
{
    public function findById(int $id): ?TaxRate
    {
        $model = GrowFinanceTaxRateModel::find($id);
        return $model ? TaxRate::reconstitute($model->toArray()) : null;
    }

    public function save(TaxRate $rate): TaxRate
    {
        $data = array_merge($rate->toArray(), [
            'id' => $rate->id,
        ]);
        if ($rate->id) {
            GrowFinanceTaxRateModel::where('id', $rate->id)->update($data);
            $model = GrowFinanceTaxRateModel::find($rate->id);
        } else {
            $model = GrowFinanceTaxRateModel::create($data);
        }
        return TaxRate::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceTaxRateModel::forBusiness($businessId)
            ->orderBy('tax_type')
            ->orderBy('effective_from', 'desc')
            ->get()
            ->map(fn($m) => TaxRate::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByType(int $businessId, string $taxType): array
    {
        return GrowFinanceTaxRateModel::forBusiness($businessId)
            ->byType($taxType)
            ->active()
            ->orderBy('effective_from', 'desc')
            ->get()
            ->map(fn($m) => TaxRate::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findDefault(int $businessId, string $taxType): ?TaxRate
    {
        $model = GrowFinanceTaxRateModel::forBusiness($businessId)
            ->byType($taxType)
            ->active()
            ->where('is_default', true)
            ->first();
        return $model ? TaxRate::reconstitute($model->toArray()) : null;
    }

    public function findEffective(int $businessId, string $taxType, \DateTimeImmutable $date): ?TaxRate
    {
        $dateStr = $date->format('Y-m-d');
        $model = GrowFinanceTaxRateModel::forBusiness($businessId)
            ->byType($taxType)
            ->active()
            ->where('effective_from', '<=', $dateStr)
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $dateStr);
            })
            ->orderBy('effective_from', 'desc')
            ->first();
        return $model ? TaxRate::reconstitute($model->toArray()) : null;
    }

    public function delete(TaxRate $rate): void
    {
        GrowFinanceTaxRateModel::where('id', $rate->id)->delete();
    }
}
