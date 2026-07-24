<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\Customer;

interface CustomerRepositoryInterface
{
    public function findById(int $id): ?Customer;

    public function findByBusiness(int $businessId, array $filters = []): array;

    public function save(Customer $entity): Customer;

    public function delete(int $id): void;

    public function countByBusiness(int $businessId): int;

    public function updatePurchaseStats(int $customerId): void;
}