<?php

declare(strict_types=1);

namespace App\Domain\LifePlus\Repositories;

use App\Domain\LifePlus\Entities\LifePlusBudget;

interface BudgetRepositoryInterface
{
    public function findById(int $id): ?LifePlusBudget;

    public function save(LifePlusBudget $budget): LifePlusBudget;

    public function delete(int $id): bool;

    public function findByUser(int $userId): array;

    public function findCurrent(int $userId): ?LifePlusBudget;
}
