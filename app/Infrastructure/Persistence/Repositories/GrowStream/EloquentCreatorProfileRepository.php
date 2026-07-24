<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Repositories\CreatorProfileRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentCreatorProfileRepository implements CreatorProfileRepositoryInterface
{
    public function findById(int $id): ?CreatorProfile
    {
        return CreatorProfile::find($id);
    }

    public function findByUserId(int $userId): ?CreatorProfile
    {
        return CreatorProfile::where('user_id', $userId)->first();
    }

    public function findAll(array $relations = []): Collection
    {
        return CreatorProfile::with($relations)->get();
    }

    public function paginate(int $perPage = 20, array $relations = []): LengthAwarePaginator
    {
        return CreatorProfile::with($relations)->paginate($perPage);
    }

    public function save(array $data): CreatorProfile
    {
        return CreatorProfile::create($data);
    }

    public function update(CreatorProfile $creator, array $data): CreatorProfile
    {
        $creator->update($data);
        return $creator->fresh();
    }

    public function delete(CreatorProfile $creator): bool
    {
        return $creator->delete();
    }

    public function verified(array $relations = []): Collection
    {
        return CreatorProfile::verified()->with($relations)->get();
    }

    public function active(array $relations = []): Collection
    {
        return CreatorProfile::active()->with($relations)->get();
    }

    public function search(string $term, array $relations = []): LengthAwarePaginator
    {
        return CreatorProfile::with($relations)
            ->where(function ($q) use ($term) {
                $q->whereHas('user', function ($userQuery) use ($term) {
                    $userQuery->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->paginate(20);
    }

    public function findWithVideoCounts(array $relations = []): Collection
    {
        return CreatorProfile::with($relations)
            ->withCount(['videos', 'videos as published_videos_count' => function ($q) {
                $q->where('is_published', true);
            }])
            ->get();
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return CreatorProfile::query();
    }
}
