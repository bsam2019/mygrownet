<?php

namespace App\Domain\Storage\Repositories;

use App\Domain\Storage\Entities\StorageFolder;

interface StorageFolderRepositoryInterface
{
    public function findById(string $id): ?StorageFolder;

    public function findByUserId(int $userId, ?string $parentId = null): array;

    public function save(StorageFolder $folder): void;

    public function delete(StorageFolder $folder): void;

    public function hasChildren(string $folderId): bool;

    public function hasFiles(string $folderId): bool;
}
