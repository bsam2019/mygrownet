<?php

namespace App\Domain\GrowMart\Repositories;

interface CouponRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findByCode(string $code): ?array;

    public function findAll(array $filters = [], int $perPage = 20): array;

    public function save(array $data): array;

    public function update(int $id, array $data): array;

    public function delete(int $id): bool;

    public function incrementUsage(int $id): void;
}
