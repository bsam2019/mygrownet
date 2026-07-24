<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\CompanyValuation;
use App\Domain\Investor\Repositories\CompanyValuationRepositoryInterface;
use App\Models\CompanyValuation as CompanyValuationModel;
use DateTimeImmutable;

class EloquentCompanyValuationRepository implements CompanyValuationRepositoryInterface
{
    public function findLatest(): ?CompanyValuation
    {
        $model = CompanyValuationModel::getLatest();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findHistory(int $months = 24): array
    {
        return CompanyValuationModel::where('valuation_date', '>=', now()->subMonths($months))
            ->orderBy('valuation_date', 'asc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    private function toDomainEntity(CompanyValuationModel $model): CompanyValuation
    {
        return CompanyValuation::fromPersistence(
            id: $model->id,
            valuationAmount: (float) $model->valuation_amount,
            valuationDate: new DateTimeImmutable($model->valuation_date),
            valuationMethod: $model->valuation_method,
            notes: $model->notes,
            assumptions: $model->assumptions,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
