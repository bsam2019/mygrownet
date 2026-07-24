<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Investment;

use App\Domain\Investment\Entities\InvestmentTier;
use App\Domain\Investment\Repositories\InvestmentTierRepositoryInterface;
use App\Models\InvestmentTier as InvestmentTierModel;

class EloquentInvestmentTierRepository implements InvestmentTierRepositoryInterface
{
    public function findById(int $id): ?InvestmentTier
    {
        $model = InvestmentTierModel::find($id);
        return $model ? InvestmentTier::reconstitute($model->toArray()) : null;
    }

    public function findActive(): array
    {
        return InvestmentTierModel::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => InvestmentTier::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findEligibleTiers(float $amount): array
    {
        return InvestmentTierModel::where('is_active', true)
            ->where('minimum_investment', '<=', $amount)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => InvestmentTier::reconstitute($m->toArray()))
            ->toArray();
    }
}
