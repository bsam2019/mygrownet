<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\AnalyticsEvent;

interface AnalyticsEventRepositoryInterface
{
    public function findByBusiness(int $businessId, array $conditions = []): array;

    public function save(AnalyticsEvent $entity): AnalyticsEvent;
}