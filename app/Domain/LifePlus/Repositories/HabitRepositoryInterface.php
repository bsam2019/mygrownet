<?php

declare(strict_types=1);

namespace App\Domain\LifePlus\Repositories;

use App\Domain\LifePlus\Entities\LifePlusHabit;

interface HabitRepositoryInterface
{
    public function findById(int $id): ?LifePlusHabit;

    public function save(LifePlusHabit $habit): LifePlusHabit;

    public function delete(int $id): bool;

    public function findByUser(int $userId): array;
}
