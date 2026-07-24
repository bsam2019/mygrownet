<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\AiUsageLog;

interface AiUsageLogRepositoryInterface
{
    public function findById(int $id): ?AiUsageLog;

    public function findByBusiness(int $businessId, array $filters = []): array;

    public function save(AiUsageLog $entity): AiUsageLog;

    public function sumCreditsByBusinessAndMonth(int $businessId, string $start, string $end): int;
}