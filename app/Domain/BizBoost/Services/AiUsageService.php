<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Entities\AiUsageLog;
use App\Domain\BizBoost\Repositories\AiUsageLogRepositoryInterface;

class AiUsageService
{
    public function __construct(
        private AiUsageLogRepositoryInterface $logRepo,
    ) {}

    public function logUsage(array $data): AiUsageLog
    {
        return $this->logRepo->save(AiUsageLog::reconstitute($data));
    }

    public function getRecentUsage(int $businessId): array
    {
        return $this->logRepo->findByBusiness($businessId, ['was_successful' => true]);
    }

    public function getMonthlyCredits(int $businessId, string $start, string $end): int
    {
        return $this->logRepo->sumCreditsByBusinessAndMonth($businessId, $start, $end);
    }
}