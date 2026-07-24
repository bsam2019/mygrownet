<?php

namespace App\Domain\GrowMart\Repositories;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findBySlug(string $slug): ?array;

    public function findAll(array $filters = []): array;

    public function findActive(array $filters = []): array;

    public function findFeatured(int $limit = 12): array;

    public function findRelated(int $productId, int $categoryId, int $limit = 6): array;

    public function findTopSelling(int $limit = 10): array;

    public function findWithLowStock(): array;

    public function countActive(): int;

    public function countAll(): int;

    public function save(array $data): array;

    public function update(int $id, array $data): array;

    public function delete(int $id): bool;

    public function findInventoryWithFilters(array $filters = []): array;

    public function updateInventory(int $inventoryId, array $data): void;

    public function findAllWarehouses(): array;
}
