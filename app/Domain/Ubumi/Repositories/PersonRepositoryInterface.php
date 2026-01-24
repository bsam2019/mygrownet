<?php

namespace App\Domain\Ubumi\Repositories;

use App\Domain\Ubumi\Entities\Person;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\PersonName;

/**
 * Person Repository Interface
 * 
 * Defines contract for person persistence
 */
interface PersonRepositoryInterface
{
    public function save(Person $person): void;

    public function findById(PersonId $id): ?Person;

    public function findBySlug(string $slug): ?Person;

    public function findByFamilyId(string $familyId): array;

    public function findSimilar(string $familyId, PersonName $name, ?int $age = null): array;

    public function delete(PersonId $id): void;

    public function softDelete(PersonId $id): void;

    public function restore(PersonId $id): void;

    public function exists(PersonId $id): bool;
}
