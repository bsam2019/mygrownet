<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Repositories;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorEarning;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CreatorEarningRepositoryInterface
{
    public function findById(int $id): ?CreatorEarning;

    public function forCreator(int $creatorId, array $relations = []): Collection;

    public function forPeriod(\DateTimeInterface $start, \DateTimeInterface $end): Collection;

    public function updateOrCreate(array $attributes, array $values): CreatorEarning;

    public function markPeriodPaid(int $creatorId, \DateTimeInterface $start, \DateTimeInterface $end): void;

    public function totalPendingForCreator(int $creatorId): float;

    public function allPending(): Collection;

    public function query(): Builder;
}
