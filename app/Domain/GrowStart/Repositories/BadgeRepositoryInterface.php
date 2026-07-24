<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Repositories;

use Illuminate\Support\Collection;

interface BadgeRepositoryInterface
{
    public function findAll(): Collection;

    public function findEarnedByJourneyId(int $journeyId): Collection;

    public function findEarnedByJourneyIdRecent(int $journeyId, int $limit = 3): Collection;
}