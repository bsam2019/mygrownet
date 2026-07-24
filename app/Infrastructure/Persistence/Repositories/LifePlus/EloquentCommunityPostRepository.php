<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\LifePlus;

use App\Domain\LifePlus\Entities\LifePlusCommunityPost;
use App\Domain\LifePlus\Repositories\CommunityPostRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\LifePlusCommunityPostModel;

class EloquentCommunityPostRepository implements CommunityPostRepositoryInterface
{
    public function findById(int $id): ?LifePlusCommunityPost
    {
        $model = LifePlusCommunityPostModel::find($id);
        return $model ? LifePlusCommunityPost::reconstitute($model->toArray()) : null;
    }

    public function save(LifePlusCommunityPost $entity): LifePlusCommunityPost
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            LifePlusCommunityPostModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = LifePlusCommunityPostModel::create($data);
        return LifePlusCommunityPost::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return LifePlusCommunityPostModel::where('id', $id)->delete() > 0;
    }

    public function findActive(array $filters = []): array
    {
        $query = LifePlusCommunityPostModel::query();

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }

        $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });

        return $query->orderBy('is_promoted', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($filters['limit'] ?? 50)
            ->get()
            ->map(fn($m) => LifePlusCommunityPost::reconstitute($m->toArray()))
            ->toArray();
    }
}
