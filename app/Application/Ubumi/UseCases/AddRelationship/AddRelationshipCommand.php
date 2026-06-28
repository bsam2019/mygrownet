<?php

namespace App\Application\Ubumi\UseCases\AddRelationship;

/**
 * Add Relationship Command
 */
class AddRelationshipCommand
{
    public function __construct(
        public readonly string $personId,
        public readonly string $relatedPersonId,
        public readonly string $relationshipType
    ) {}
}
