<?php

namespace App\Application\Ubumi\UseCases\AddRelationship;

use App\Domain\Ubumi\Services\RelationshipService;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\RelationshipType;

/**
 * Add Relationship Handler
 */
class AddRelationshipHandler
{
    public function __construct(
        private RelationshipService $relationshipService
    ) {}

    public function handle(AddRelationshipCommand $command): void
    {
        $personId = PersonId::fromString($command->personId);
        $relatedPersonId = PersonId::fromString($command->relatedPersonId);
        $relationshipType = RelationshipType::fromString($command->relationshipType);

        // Validate the relationship
        if (!$this->relationshipService->validateRelationship($personId, $relatedPersonId, $relationshipType)) {
            throw new \DomainException('Invalid relationship');
        }

        // Create the relationship
        $this->relationshipService->createRelationship($personId, $relatedPersonId, $relationshipType);
    }
}
