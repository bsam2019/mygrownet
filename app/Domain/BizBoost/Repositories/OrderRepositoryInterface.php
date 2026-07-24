<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\Order;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function findByBusiness(int $businessId, array $filters = []): array;

    public function save(Order $entity): Order;

    public function delete(int $id): void;

    public function countByBusiness(int $businessId): int;
}