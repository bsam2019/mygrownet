<?php

namespace App\Domain\Ubumi\Repositories;

use App\Domain\Ubumi\Entities\Family;
use App\Domain\Ubumi\ValueObjects\FamilyId;

/**
 * Family Repository Interface
 * 
 * Defines contract for family persistence
 */
interface FamilyRepositoryInterface
{
    public function save(Family $family): void;

    public function findById(FamilyId $id): ?Family;

    public function findBySlug(string $slug): ?Family;

    public function findByAdminUserId(int $userId): array;

    public function slugExists(string $slug, ?FamilyId $excludeId = null): bool;

    public function delete(FamilyId $id): void;

    public function exists(FamilyId $id): bool;
}
