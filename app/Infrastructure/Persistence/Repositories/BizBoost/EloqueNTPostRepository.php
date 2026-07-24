<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\Post;
use App\Domain\BizBoost\Repositories\PostRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;

class EloquentPostRepository implements PostRepositoryInterface
{
    public function findById(int $id): ?Post
    {
        $model = BizBoostPostModel::find($id);
        return $model ? Post::reconstitute($model->toArray()) : null;
    }

    public function findByBusiness(int $businessId, array $filters = []): array
    {
        $query = BizBoostPostModel::where('business_id', $businessId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('caption', 'like', "%{$search}%");
        }

        return $query->orderByDesc('created_at')->get()
            ->map(fn($m) => Post::reconstitute($m->toArray()))
            ->toArray();
    }

    public function save(Post $entity): Post
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            BizBoostPostModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = BizBoostPostModel::create($data);
        return Post::reconstitute($model->toArray());
    }

    public function delete(int $id): void
    {
        BizBoostPostModel::destroy($id);
    }

    public function countByBusiness(int $businessId, array $conditions = []): int
    {
        $query = BizBoostPostModel::where('business_id', $businessId);
        if (!empty($conditions['status'])) {
            $query->where('status', $conditions['status']);
        }
        return $query->count();
    }

    public function countByBusinessAndMonth(int $businessId, string $start, string $end): int
    {
        return BizBoostPostModel::where('business_id', $businessId)
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    public function findByBusinessDateRange(int $businessId, string $start, string $end): array
    {
        return BizBoostPostModel::where('business_id', $businessId)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('scheduled_at', [$start, $end])
                    ->orWhereBetween('published_at', [$start, $end])
                    ->orWhereBetween('created_at', [$start, $end]);
            })
            ->get()
            ->map(fn($m) => Post::reconstitute($m->toArray()))
            ->toArray();
    }
}