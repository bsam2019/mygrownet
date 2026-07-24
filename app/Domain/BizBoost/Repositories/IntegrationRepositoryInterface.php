<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\Integration;

interface IntegrationRepositoryInterface
{
    public function findById(int $id): ?Integration;

    public function findByBusiness(int $businessId): array;

    public function findActiveByBusiness(int $businessId): array;

    public function findByProvider(int $businessId, string $provider): ?Integration;

    public function save(Integration $entity): Integration;

    public function delete(int $id): void;
}