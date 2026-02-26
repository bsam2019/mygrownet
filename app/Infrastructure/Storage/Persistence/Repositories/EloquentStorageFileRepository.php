<?php

namespace App\Infrastructure\Storage\Persistence\Repositories;

use App\Domain\Storage\Entities\StorageFile as StorageFileEntity;
use App\Domain\Storage\Repositories\StorageFileRepositoryInterface;
use App\Domain\Storage\ValueObjects\FileSize;
use App\Domain\Storage\ValueObjects\S3Path;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageFile as StorageFileModel;

class EloquentStorageFileRepository implements StorageFileRepositoryInterface
{
    public function findById(string $id): ?StorageFileEntity
    {
        $model = StorageFileModel::find($id);
        
        if (!$model) {
            return null;
        }
        
        return $this->toDomainEntity($model);
    }

    public function findByUserId(int $userId, ?string $folderId = null): array
    {
        $query = StorageFileModel::forUser($userId)
            ->notDeleted()
            ->inFolder($folderId);
        
        return $query->get()->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function save(StorageFileEntity $file): void
    {
        // Generate stored name from S3 key (filename part)
        $s3Key = $file->getS3Path()->getKey();
        $storedName = basename($s3Key);
        
        StorageFileModel::updateOrCreate(
            ['id' => $file->getId()],
            [
                'user_id' => $file->getUserId(),
                'folder_id' => $file->getFolderId(),
                'original_name' => $file->getOriginalName(),
                'stored_name' => $storedName,
                'extension' => $file->getExtension(),
                'mime_type' => $file->getMimeType()->getValue(),
                'size_bytes' => $file->getSize()->toBytes(),
                's3_bucket' => $file->getS3Path()->getBucket(),
                's3_key' => $file->getS3Path()->getKey(),
                'checksum' => $file->getChecksum(),
            ]
        );
    }

    public function delete(StorageFileEntity $file): void
    {
        $model = StorageFileModel::find($file->getId());
        
        if ($model) {
            $model->delete();
        }
    }

    public function countByUserId(int $userId): int
    {
        return StorageFileModel::forUser($userId)->notDeleted()->count();
    }

    public function getTotalSizeByUserId(int $userId): int
    {
        return StorageFileModel::forUser($userId)
            ->notDeleted()
            ->sum('size_bytes');
    }

    private function toDomainEntity(StorageFileModel $model): StorageFileEntity
    {
        $entity = StorageFileEntity::create(
            $model->id,
            $model->user_id,
            $model->folder_id,
            $model->original_name,
            $model->mime_type,
            FileSize::fromBytes($model->size_bytes),
            S3Path::create($model->s3_bucket, $model->s3_key)
        );

        if ($model->checksum) {
            $entity->setChecksum($model->checksum);
        }

        return $entity;
    }
}
