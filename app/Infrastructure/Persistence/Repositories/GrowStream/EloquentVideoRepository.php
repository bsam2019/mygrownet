<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Repositories\VideoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentVideoRepository implements VideoRepositoryInterface
{
    public function findById(int $id): ?Video
    {
        return Video::find($id);
    }

    public function findBySlug(string $slug): ?Video
    {
        return Video::where('slug', $slug)->first();
    }

    public function findByUuid(string $uuid): ?Video
    {
        return Video::where('uuid', $uuid)->first();
    }

    public function findAll(array $relations = []): Collection
    {
        return Video::with($relations)->get();
    }

    public function paginate(int $perPage = 20, array $relations = []): LengthAwarePaginator
    {
        return Video::with($relations)->paginate($perPage);
    }

    public function save(array $data): Video
    {
        return Video::create($data);
    }

    public function update(Video $video, array $data): Video
    {
        $video->update($data);
        return $video->fresh();
    }

    public function delete(Video $video): bool
    {
        return $video->delete();
    }

    public function published(array $relations = []): Collection
    {
        return Video::published()->with($relations)->get();
    }

    public function paginatePublished(int $perPage = 20, array $relations = []): LengthAwarePaginator
    {
        return Video::published()->ready()->with($relations)->paginate($perPage);
    }

    public function featured(int $limit = 10, array $relations = []): Collection
    {
        return Video::published()->ready()->featured()
            ->with($relations)
            ->latest('featured_at')
            ->limit($limit)
            ->get();
    }

    public function trending(int $days = 7, int $limit = 20, array $relations = []): Collection
    {
        return Video::published()->ready()
            ->with($relations)
            ->trending($days)
            ->limit($limit)
            ->get();
    }

    public function search(string $term, array $relations = []): LengthAwarePaginator
    {
        return Video::published()->ready()
            ->with($relations)
            ->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            })
            ->paginate(20);
    }

    public function findByContentType(string $type, array $relations = []): Collection
    {
        return Video::published()->ready()
            ->with($relations)
            ->byContentType($type)
            ->get();
    }

    public function findByCreator(int $creatorId, array $relations = []): Collection
    {
        return Video::where('creator_id', $creatorId)
            ->with($relations)
            ->get();
    }

    public function findBySeries(int $seriesId, array $relations = []): Collection
    {
        return Video::where('series_id', $seriesId)
            ->with($relations)
            ->orderBy('season_number')
            ->orderBy('episode_number')
            ->get();
    }

    public function updateInBulk(array $ids, array $data): void
    {
        Video::whereIn('id', $ids)->update($data);
    }

    public function bulkDelete(array $ids): void
    {
        Video::whereIn('id', $ids)->delete();
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return Video::query();
    }
}
