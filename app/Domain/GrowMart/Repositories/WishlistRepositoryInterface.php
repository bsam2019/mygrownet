<?php

namespace App\Domain\GrowMart\Repositories;

interface WishlistRepositoryInterface
{
    public function findByUser(int $userId, int $perPage = 20): array;

    public function findProductIdsByUser(int $userId): array;

    public function isWishlisted(int $userId, int $productId): bool;

    public function findByUserAndProduct(int $userId, int $productId): ?array;

    public function add(int $userId, int $productId): array;

    public function remove(int $userId, int $productId): void;

    public function exists(int $userId, int $productId): bool;
}
