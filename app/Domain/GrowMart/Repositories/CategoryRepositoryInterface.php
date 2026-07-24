<?php

namespace App\Domain\GrowMart\Repositories;

interface CategoryRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findAll(array $filters = []): array;

    public function findActive(): array;

    public function findParentCategories(): array;

    public function findWithChildren(int $id): ?array;

    public function countAll(): int;

    public function save(array $data): array;

    public function update(int $id, array $data): array;

    public function delete(int $id): bool;

    public function productCount(int $categoryId): int;
}
