<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\Category;

interface CategoryRepositoryInterface
{
    public function findById(int $id): ?Category;

    public function findByBusiness(int $businessId): array;

    public function findByName(int $businessId, string $name): ?Category;

    public function save(Category $entity): Category;

    public function delete(int $id): void;

    public function getNextSortOrder(int $businessId): int;
}