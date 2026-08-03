<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorEarning;
use App\Domain\GrowStream\Repositories\CreatorEarningRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentCreatorEarningRepository implements CreatorEarningRepositoryInterface
{
    public function findById(int $id): ?CreatorEarning
    {
        return CreatorEarning::find($id);
    }

    public function forCreator(int $creatorId, array $relations = []): Collection
    {
        return CreatorEarning::with($relations)
            ->where('creator_id', $creatorId)
            ->orderBy('period_start', 'desc')
            ->get();
    }

    public function forPeriod(\DateTimeInterface $start, \DateTimeInterface $end): Collection
    {
        return CreatorEarning::where('period_start', '>=', $start)
            ->where('period_end', '<=', $end)
            ->get();
    }

    public function updateOrCreate(array $attributes, array $values): CreatorEarning
    {
        return CreatorEarning::updateOrCreate($attributes, $values);
    }

    public function markPeriodPaid(int $creatorId, \DateTimeInterface $start, \DateTimeInterface $end): void
    {
        CreatorEarning::where('creator_id', $creatorId)
            ->where('period_start', $start)
            ->where('period_end', $end)
            ->update(['status' => 'paid']);
    }

    public function totalPendingForCreator(int $creatorId): float
    {
        return (float) CreatorEarning::where('creator_id', $creatorId)
            ->where('status', 'pending')
            ->sum('earned_amount');
    }

    public function allPending(): Collection
    {
        return CreatorEarning::with('creator')
            ->where('status', 'pending')
            ->orderBy('period_start', 'asc')
            ->get();
    }

    public function query(): Builder
    {
        return CreatorEarning::query();
    }
}
