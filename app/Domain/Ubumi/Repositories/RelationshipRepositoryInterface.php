<?php

namespace App\Domain\Ubumi\Repositories;

use App\Domain\Ubumi\Entities\Relationship;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\RelationshipType;

/**
 * Relationship Repository Interface
 * 
 * Defines contract for relationship persistence
 */
interface RelationshipRepositoryInterface
{
    public function save(Relationship $relationship): void;

    public function findById(int $id): ?Relationship;

    public function findByPersonId(PersonId $personId): array;

    public function findRelationship(PersonId $personId, PersonId $relatedPersonId): ?Relationship;

    public function exists(PersonId $personId, PersonId $relatedPersonId, RelationshipType $type): bool;

    public function delete(int $id): void;

    public function deleteAllForPerson(PersonId $personId): void;
}
