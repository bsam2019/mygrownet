<?php

namespace App\Domain\Marketplace\Repositories;

use App\Domain\Marketplace\Entities\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function findBySlug(string $slug): ?Product;
    public function findBySeller(int $sellerId, array $filters = [], int $perPage = 20): array;
    public function findActive(array $filters = [], int $perPage = 20): array;
    public function findByCategory(int $categoryId, array $filters = [], int $perPage = 20): array;
    public function search(string $query, array $filters = [], int $perPage = 20): array;
    public function save(Product $product): Product;
    public function updateStatus(int $productId, string $status): void;
    public function decrementStock(int $productId, int $quantity): void;
    public function incrementStock(int $productId, int $quantity): void;
    public function delete(int $productId): void;
}
