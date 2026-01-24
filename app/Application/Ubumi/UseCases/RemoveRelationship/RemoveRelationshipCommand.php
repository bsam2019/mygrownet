<?php

namespace App\Application\Ubumi\UseCases\RemoveRelationship;

/**
 * Remove Relationship Command
 */
class RemoveRelationshipCommand
{
    public function __construct(
        public readonly int $relationshipId
    ) {}
}
