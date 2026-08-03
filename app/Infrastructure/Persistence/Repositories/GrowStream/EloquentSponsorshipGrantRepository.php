<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\SponsorshipGrant;
use App\Domain\GrowStream\Repositories\SponsorshipGrantRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EloquentSponsorshipGrantRepository implements SponsorshipGrantRepositoryInterface
{
    public function findById(int $id): ?SponsorshipGrant
    {
        return SponsorshipGrant::find($id);
    }

    public function forCreator(int $creatorId, array $relations = []): Collection
    {
        return SponsorshipGrant::with($relations)
            ->where('creator_id', $creatorId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function paginate(array $filters = [], int $perPage = 20, array $relations = []): LengthAwarePaginator
    {
        $query = SponsorshipGrant::with($relations);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['creator_id'])) {
            $query->where('creator_id', $filters['creator_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): SponsorshipGrant
    {
        return SponsorshipGrant::create($data);
    }

    public function update(SponsorshipGrant $grant, array $data): SponsorshipGrant
    {
        $grant->update($data);

        return $grant->fresh();
    }

    public function totalApproved(): float
    {
        return (float) SponsorshipGrant::whereIn('status', ['approved', 'disbursed', 'completed'])
            ->sum('amount');
    }

    public function totalDisbursed(): float
    {
        return (float) SponsorshipGrant::whereIn('status', ['disbursed', 'completed'])
            ->sum('amount');
    }

    public function query(): Builder
    {
        return SponsorshipGrant::query();
    }
}
