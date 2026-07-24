<?php

declare(strict_types=1);

namespace App\Domain\Investment\Repositories;

use App\Domain\Investment\Entities\InvestmentTier;

interface InvestmentTierRepositoryInterface
{
    public function findById(int $id): ?InvestmentTier;

    public function findActive(): array;

    public function findEligibleTiers(float $amount): array;
}
