<?php

namespace App\Domain\GrowMart\Repositories;

interface ReviewRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findByProduct(int $productId, bool $approvedOnly = true): array;

    public function findByUser(int $userId): array;

    public function findAll(array $filters = [], int $perPage = 20): array;

    public function findUserReview(int $userId, int $productId): ?array;

    public function save(array $data): array;

    public function update(int $id, array $data): array;

    public function delete(int $id): bool;

    public function getAverageRating(int $productId): float;

    public function countPending(): int;
}
