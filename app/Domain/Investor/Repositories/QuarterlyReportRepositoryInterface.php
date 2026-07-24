<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\QuarterlyReport;

interface QuarterlyReportRepositoryInterface
{
    public function findLatest(): ?QuarterlyReport;

    public function findById(int $id): ?QuarterlyReport;
}
