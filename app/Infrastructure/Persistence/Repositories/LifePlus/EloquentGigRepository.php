<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\LifePlus;

use App\Domain\LifePlus\Entities\LifePlusGig;
use App\Domain\LifePlus\Repositories\GigRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\LifePlusGigModel;

class EloquentGigRepository implements GigRepositoryInterface
{
    public function findById(int $id): ?LifePlusGig
    {
        $model = LifePlusGigModel::find($id);
        return $model ? LifePlusGig::reconstitute($model->toArray()) : null;
    }

    public function save(LifePlusGig $entity): LifePlusGig
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            LifePlusGigModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = LifePlusGigModel::create($data);
        return LifePlusGig::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return LifePlusGigModel::where('id', $id)->delete() > 0;
    }

    public function findOpen(array $filters = []): array
    {
        $query = LifePlusGigModel::where('status', 'open');

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($filters['limit'] ?? 50)
            ->get()
            ->map(fn($m) => LifePlusGig::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByUser(int $userId): array
    {
        return LifePlusGigModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => LifePlusGig::reconstitute($m->toArray()))
            ->toArray();
    }
}
