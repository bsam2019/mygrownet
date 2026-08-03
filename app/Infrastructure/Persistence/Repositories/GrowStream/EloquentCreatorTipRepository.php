<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorTip;
use App\Domain\GrowStream\Repositories\CreatorTipRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentCreatorTipRepository implements CreatorTipRepositoryInterface
{
    public function findById(int $id): ?CreatorTip
    {
        return CreatorTip::find($id);
    }

    public function forCreator(int $creatorId, array $relations = []): Collection
    {
        return CreatorTip::with($relations)
            ->where('creator_id', $creatorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function paginateForCreator(int $creatorId, int $perPage = 20, array $relations = []): LengthAwarePaginator
    {
        return CreatorTip::with($relations)
            ->where('creator_id', $creatorId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): CreatorTip
    {
        return CreatorTip::create($data);
    }

    public function totalForCreator(int $creatorId): float
    {
        return (float) CreatorTip::where('creator_id', $creatorId)
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function countForCreator(int $creatorId): int
    {
        return CreatorTip::where('creator_id', $creatorId)
            ->where('status', 'completed')
            ->count();
    }

    public function query(): Builder
    {
        return CreatorTip::query();
    }
}
