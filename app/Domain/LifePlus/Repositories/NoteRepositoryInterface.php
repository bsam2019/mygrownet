<?php

declare(strict_types=1);

namespace App\Domain\LifePlus\Repositories;

use App\Domain\LifePlus\Entities\LifePlusNote;

interface NoteRepositoryInterface
{
    public function findById(int $id): ?LifePlusNote;

    public function save(LifePlusNote $note): LifePlusNote;

    public function delete(int $id): bool;

    public function findByUser(int $userId): array;

    public function findByLocalId(int $userId, string $localId): ?LifePlusNote;
}
