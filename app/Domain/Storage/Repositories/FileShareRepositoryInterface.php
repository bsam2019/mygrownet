<?php

namespace App\Domain\Storage\Repositories;

interface FileShareRepositoryInterface
{
    public function findByFileId(string $fileId): array;

    public function findById(string $shareId): ?array;

    public function findByToken(string $token): ?array;

    public function create(array $data): array;

    public function delete(string $shareId): void;

    public function incrementDownloadCount(string $shareId): void;
}