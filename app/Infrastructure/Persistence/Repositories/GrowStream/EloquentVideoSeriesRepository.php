<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowStream;

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\VideoSeries;
use App\Domain\GrowStream\Repositories\VideoSeriesRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EloquentVideoSeriesRepository implements VideoSeriesRepositoryInterface
{
    public function findById(int $id): ?VideoSeries
    {
        return VideoSeries::find($id);
    }

    public function findBySlug(string $slug): ?VideoSeries
    {
        return VideoSeries::where('slug', $slug)->first();
    }

    public function findAll(array $relations = []): Collection
    {
        return VideoSeries::with($relations)->get();
    }

    public function paginate(int $perPage = 20, array $relations = []): LengthAwarePaginator
    {
        return VideoSeries::with($relations)->paginate($perPage);
    }

    public function save(array $data): VideoSeries
    {
        return VideoSeries::create($data);
    }

    public function update(VideoSeries $series, array $data): VideoSeries
    {
        $series->update($data);
        return $series->fresh();
    }

    public function delete(VideoSeries $series): bool
    {
        return $series->delete();
    }

    public function published(array $relations = []): Collection
    {
        return VideoSeries::published()->with($relations)->get();
    }

    public function paginatePublished(int $perPage = 20, array $relations = []): LengthAwarePaginator
    {
        return VideoSeries::published()->with($relations)->paginate($perPage);
    }

    public function search(string $term, array $relations = []): LengthAwarePaginator
    {
        return VideoSeries::published()
            ->with($relations)
            ->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            })
            ->paginate(20);
    }

    public function findByType(string $type, array $relations = []): Collection
    {
        return VideoSeries::published()
            ->with($relations)
            ->byType($type)
            ->get();
    }

    public function findByCreator(int $creatorId, array $relations = []): Collection
    {
        return VideoSeries::where('creator_id', $creatorId)
            ->with($relations)
            ->get();
    }

    public function query(): \Illuminate\Database\Eloquent\Builder
    {
        return VideoSeries::query();
    }
}
