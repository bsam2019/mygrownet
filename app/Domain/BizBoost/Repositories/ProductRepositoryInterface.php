<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;

    public function findByBusiness(int $businessId, array $filters = []): array;

    public function findActiveByBusiness(int $businessId, array $filters = []): array;

    public function save(Product $entity): Product;

    public function delete(int $id): void;

    public function countByBusiness(int $businessId): int;

    public function getCategories(int $businessId): array;
}