<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\CompanyValuation;

interface CompanyValuationRepositoryInterface
{
    public function findLatest(): ?CompanyValuation;

    public function findHistory(int $months = 24): array;
}
