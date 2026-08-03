<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorPayout;
use App\Domain\GrowStream\Repositories\CreatorPayoutRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentCreatorPayoutRepository implements CreatorPayoutRepositoryInterface
{
    public function findById(int $id): ?CreatorPayout
    {
        return CreatorPayout::find($id);
    }

    public function forCreator(int $creatorId, array $relations = []): Collection
    {
        return CreatorPayout::with($relations)
            ->where('creator_id', $creatorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function paginateForCreator(int $creatorId, int $perPage = 20, array $relations = []): LengthAwarePaginator
    {
        return CreatorPayout::with($relations)
            ->where('creator_id', $creatorId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): CreatorPayout
    {
        return CreatorPayout::create($data);
    }

    public function update(CreatorPayout $payout, array $data): CreatorPayout
    {
        $payout->update($data);

        return $payout->fresh();
    }

    public function totalPaidForCreator(int $creatorId): float
    {
        return (float) CreatorPayout::where('creator_id', $creatorId)
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function query(): Builder
    {
        return CreatorPayout::query();
    }
}
