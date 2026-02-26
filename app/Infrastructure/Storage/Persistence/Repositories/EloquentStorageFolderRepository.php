<?php

namespace App\Infrastructure\Storage\Persistence\Repositories;

use App\Domain\Storage\Entities\StorageFolder as StorageFolderEntity;
use App\Domain\Storage\Repositories\StorageFolderRepositoryInterface;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageFolder as StorageFolderModel;

class EloquentStorageFolderRepository implements StorageFolderRepositoryInterface
{
    public function findById(string $id): ?StorageFolderEntity
    {
        $model = StorageFolderModel::find($id);
        
        if (!$model) {
            return null;
        }
        
        return $this->toDomainEntity($model);
    }

    public function findByUserId(int $userId, ?string $parentId = null): array
    {
        $query = StorageFolderModel::forUser($userId)->inFolder($parentId);
        
        return $query->get()->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function save(StorageFolderEntity $folder): void
    {
        StorageFolderModel::updateOrCreate(
            ['id' => $folder->getId()],
            [
                'user_id' => $folder->getUserId(),
                'parent_id' => $folder->getParentId(),
                'name' => $folder->getName(),
                'path_cache' => $folder->getPathCache(),
            ]
        );
    }

    public function delete(StorageFolderEntity $folder): void
    {
        $model = StorageFolderModel::find($folder->getId());
        
        if ($model) {
            $model->delete();
        }
    }

    public function hasChildren(string $folderId): bool
    {
        return StorageFolderModel::where('parent_id', $folderId)->exists();
    }

    public function hasFiles(string $folderId): bool
    {
        $model = StorageFolderModel::find($folderId);
        return $model ? $model->hasFiles() : false;
    }

    private function toDomainEntity(StorageFolderModel $model): StorageFolderEntity
    {
        return StorageFolderEntity::create(
            $model->id,
            $model->user_id,
            $model->parent_id,
            $model->name
        );
    }
}
