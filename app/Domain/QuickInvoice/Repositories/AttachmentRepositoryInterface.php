<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Repositories;

interface AttachmentRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findByUser(int $userId): array;

    public function create(array $data): array;

    public function update(int $id, array $data): ?array;

    public function delete(int $id): bool;

    public function findByUserAndIds(int $userId, array $ids): array;
}