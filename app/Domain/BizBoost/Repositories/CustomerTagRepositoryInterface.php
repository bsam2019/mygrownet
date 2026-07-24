<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\CustomerTag;

interface CustomerTagRepositoryInterface
{
    public function findById(int $id): ?CustomerTag;

    public function findByBusiness(int $businessId): array;

    public function save(CustomerTag $entity): CustomerTag;

    public function delete(int $id): void;
}