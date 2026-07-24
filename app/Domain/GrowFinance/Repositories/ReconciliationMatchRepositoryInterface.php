<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\ReconciliationMatch;

interface ReconciliationMatchRepositoryInterface
{
    public function findById(int $id): ?ReconciliationMatch;

    public function save(ReconciliationMatch $reconciliationMatch): ReconciliationMatch;

    public function findByPeriod(int $reconciliationPeriodId): array;

    public function findByStatementLine(int $statementLineId): array;
}
