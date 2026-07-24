<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\Campaign;

interface CampaignRepositoryInterface
{
    public function findById(int $id): ?Campaign;

    public function findByBusiness(int $businessId, array $filters = []): array;

    public function save(Campaign $entity): Campaign;

    public function delete(int $id): void;

    public function countByBusiness(int $businessId, ?string $status = null): int;
}