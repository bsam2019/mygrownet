<?php

declare(strict_types=1);

namespace App\Domain\LifePlus\Repositories;

use App\Domain\LifePlus\Entities\LifePlusTask;

interface TaskRepositoryInterface
{
    public function findById(int $id): ?LifePlusTask;

    public function save(LifePlusTask $task): LifePlusTask;

    public function delete(int $id): bool;

    public function findByUser(int $userId, array $filters = []): array;

    public function findByLocalId(int $userId, string $localId): ?LifePlusTask;

    public function getStats(int $userId): array;

    public function getForMonth(int $userId, string $month): array;
}
