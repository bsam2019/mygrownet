<?php

declare(strict_types=1);

namespace App\Domain\LifePlus\Repositories;

use App\Domain\LifePlus\Entities\LifePlusGig;

interface GigRepositoryInterface
{
    public function findById(int $id): ?LifePlusGig;

    public function save(LifePlusGig $gig): LifePlusGig;

    public function delete(int $id): bool;

    public function findOpen(array $filters = []): array;

    public function findByUser(int $userId): array;
}
