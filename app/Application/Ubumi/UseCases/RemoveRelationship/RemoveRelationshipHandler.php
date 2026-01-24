<?php

namespace App\Application\Ubumi\UseCases\RemoveRelationship;

use App\Domain\Ubumi\Services\RelationshipService;

/**
 * Remove Relationship Handler
 */
class RemoveRelationshipHandler
{
    public function __construct(
        private RelationshipService $relationshipService
    ) {}

    public function handle(RemoveRelationshipCommand $command): void
    {
        $this->relationshipService->deleteRelationship($command->relationshipId);
    }
}
