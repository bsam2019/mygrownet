<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\LifePlus;

use App\Domain\LifePlus\Entities\LifePlusNote;
use App\Domain\LifePlus\Repositories\NoteRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\LifePlusNoteModel;

class EloquentNoteRepository implements NoteRepositoryInterface
{
    public function findById(int $id): ?LifePlusNote
    {
        $model = LifePlusNoteModel::find($id);
        return $model ? LifePlusNote::reconstitute($model->toArray()) : null;
    }

    public function save(LifePlusNote $entity): LifePlusNote
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            LifePlusNoteModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = LifePlusNoteModel::create($data);
        return LifePlusNote::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return LifePlusNoteModel::where('id', $id)->delete() > 0;
    }

    public function findByUser(int $userId): array
    {
        return LifePlusNoteModel::where('user_id', $userId)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($m) => LifePlusNote::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByLocalId(int $userId, string $localId): ?LifePlusNote
    {
        $model = LifePlusNoteModel::where('user_id', $userId)
            ->where('local_id', $localId)
            ->first();
        return $model ? LifePlusNote::reconstitute($model->toArray()) : null;
    }
}
