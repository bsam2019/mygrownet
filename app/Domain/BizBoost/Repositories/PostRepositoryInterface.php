<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\Post;

interface PostRepositoryInterface
{
    public function findById(int $id): ?Post;

    public function findByBusiness(int $businessId, array $filters = []): array;

    public function save(Post $entity): Post;

    public function delete(int $id): void;

    public function countByBusiness(int $businessId, array $conditions = []): int;

    public function countByBusinessAndMonth(int $businessId, string $start, string $end): int;

    public function findByBusinessDateRange(int $businessId, string $start, string $end): array;
}