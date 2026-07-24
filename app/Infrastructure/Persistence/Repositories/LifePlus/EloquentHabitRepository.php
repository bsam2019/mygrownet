<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\LifePlus;

use App\Domain\LifePlus\Entities\LifePlusHabit;
use App\Domain\LifePlus\Repositories\HabitRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\LifePlusHabitModel;

class EloquentHabitRepository implements HabitRepositoryInterface
{
    public function findById(int $id): ?LifePlusHabit
    {
        $model = LifePlusHabitModel::find($id);
        return $model ? LifePlusHabit::reconstitute($model->toArray()) : null;
    }

    public function save(LifePlusHabit $entity): LifePlusHabit
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            LifePlusHabitModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = LifePlusHabitModel::create($data);
        return LifePlusHabit::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return LifePlusHabitModel::where('id', $id)->delete() > 0;
    }

    public function findByUser(int $userId): array
    {
        return LifePlusHabitModel::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => LifePlusHabit::reconstitute($m->toArray()))
            ->toArray();
    }
}
