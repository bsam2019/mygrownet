<?php

namespace App\Domain\Marketplace\Repositories;

interface PayoutRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findBySeller(int $sellerId, int $perPage = 20): array;
    public function findAll(array $filters = [], int $perPage = 20): array;
    public function hasPendingPayout(int $sellerId): bool;
    public function create(array $data): array;
    public function update(int $payoutId, array $data): array;
    public function getStats(): array;
}
