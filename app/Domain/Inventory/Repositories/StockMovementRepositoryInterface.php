<?php

namespace App\Domain\Inventory\Repositories;

use App\Domain\Inventory\Entities\StockMovement;

interface StockMovementRepositoryInterface
{
    public function findById(int $id): ?StockMovement;

    public function findByItem(int $itemId, array $filters = []): array;

    public function findAllByUser(int $userId, array $filters = []): array;

    public function findRecentByUser(int $userId, int $limit = 10): array;

    public function countRecentByUser(int $userId, int $days = 7): int;

    public function findInDateRange(int $userId, string $startDate, ?string $endDate = null): array;

    public function save(StockMovement $movement): StockMovement;
}
