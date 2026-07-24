<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowStart\Repositories;

use App\Domain\GrowStart\Repositories\BadgeRepositoryInterface;
use App\Models\GrowStart\Badge;
use Illuminate\Support\Collection;

class EloquentBadgeRepository implements BadgeRepositoryInterface
{
    public function findAll(): Collection
    {
        return Badge::all();
    }

    public function findEarnedByJourneyId(int $journeyId): Collection
    {
        return Badge::whereHas('journeys', function ($q) use ($journeyId) {
            $q->where('growstart_user_badges.user_journey_id', $journeyId);
        })->get();
    }

    public function findEarnedByJourneyIdRecent(int $journeyId, int $limit = 3): Collection
    {
        return Badge::whereHas('journeys', function ($q) use ($journeyId) {
            $q->where('growstart_user_badges.user_journey_id', $journeyId);
        })->latest()->take($limit)->get();
    }
}