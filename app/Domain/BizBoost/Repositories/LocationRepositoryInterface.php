<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\Location;

interface LocationRepositoryInterface
{
    public function findById(int $id): ?Location;

    public function findByBusiness(int $businessId): array;

    public function findPrimary(int $businessId): ?Location;

    public function save(Location $entity): Location;

    public function delete(int $id): void;

    public function countByBusiness(int $businessId): int;
}