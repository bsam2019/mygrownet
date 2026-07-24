<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\RiskAssessment;

interface RiskAssessmentRepositoryInterface
{
    public function findLatest(): ?RiskAssessment;

    public function findHistory(int $limit = 12): array;
}
