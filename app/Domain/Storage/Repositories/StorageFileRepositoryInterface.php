<?php

namespace App\Domain\Storage\Repositories;

use App\Domain\Storage\Entities\StorageFile;

interface StorageFileRepositoryInterface
{
    public function findById(string $id): ?StorageFile;

    public function findByUserId(int $userId, ?string $folderId = null): array;

    public function save(StorageFile $file): void;

    public function delete(StorageFile $file): void;

    public function countByUserId(int $userId): int;

    public function getTotalSizeByUserId(int $userId): int;
}
