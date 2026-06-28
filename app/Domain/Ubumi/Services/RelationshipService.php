<?php

namespace App\Domain\Ubumi\Services;

use App\Domain\Ubumi\Entities\Relationship;
use App\Domain\Ubumi\Repositories\RelationshipRepositoryInterface;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\RelationshipType;

/**
 * Relationship Service
 * 
 * Handles business logic for managing relationships
 */
class RelationshipService
{
    public function __construct(
        private RelationshipRepositoryInterface $relationshipRepository,
        private PersonRepositoryInterface $personRepository
    ) {}

    /**
     * Create a bidirectional relationship between two persons
     * 
     * For reciprocal relationships (sibling, spouse, etc.), creates both directions
     * For non-reciprocal (parent-child), creates the inverse automatically
     */
    public function createRelationship(
        PersonId $personId,
        PersonId $relatedPersonId,
        RelationshipType $type
    ): void {
        // Check if relationship already exists
        if ($this->relationshipRepository->exists($personId, $relatedPersonId, $type)) {
            throw new \DomainException('This relationship already exists');
        }

        // Create the primary relationship
        $relationship = Relationship::create($personId, $relatedPersonId, $type);
        $this->relationshipRepository->save($relationship);

        // Create the inverse relationship
        $inverseType = $type->getInverse();
        $inverseRelationship = Relationship::create($relatedPersonId, $personId, $inverseType);
        $this->relationshipRepository->save($inverseRelationship);
    }

    /**
     * Update a relationship type
     */
    public function updateRelationship(
        int $relationshipId,
        RelationshipType $newType
    ): void {
        $relationship = $this->relationshipRepository->findById($relationshipId);
        
        if (!$relationship) {
            throw new \DomainException('Relationship not found');
        }

        // Update the primary relationship
        $relationship->updateType($newType);
        $this->relationshipRepository->save($relationship);

        // Find and update the inverse relationship
        $inverseRelationship = $this->relationshipRepository->findRelationship(
            $relationship->getRelatedPersonId(),
            $relationship->getPersonId()
        );

        if ($inverseRelationship) {
            $inverseRelationship->updateType($newType->getInverse());
            $this->relationshipRepository->save($inverseRelationship);
        }
    }

    /**
     * Delete a relationship (and its inverse)
     */
    public function deleteRelationship(int $relationshipId): void
    {
        $relationship = $this->relationshipRepository->findById($relationshipId);
        
        if (!$relationship) {
            throw new \DomainException('Relationship not found');
        }

        // Delete the primary relationship
        $this->relationshipRepository->delete($relationshipId);

        // Find and delete the inverse relationship
        $inverseRelationship = $this->relationshipRepository->findRelationship(
            $relationship->getRelatedPersonId(),
            $relationship->getPersonId()
        );

        if ($inverseRelationship) {
            $this->relationshipRepository->delete($inverseRelationship->getId());
        }
    }

    /**
     * Get all relationships for a person
     */
    public function getPersonRelationships(PersonId $personId): array
    {
        return $this->relationshipRepository->findByPersonId($personId);
    }

    /**
     * Validate that a relationship makes biological sense
     * 
     * Checks:
     * - No self-relationships
     * - Parent must be at least 12 years older than child
     * - Grandparent must be at least 24 years older than grandchild
     * - Siblings should be within reasonable age range (optional warning)
     */
    public function validateRelationship(
        PersonId $personId,
        PersonId $relatedPersonId,
        RelationshipType $type
    ): bool {
        // Prevent self-relationships
        if ($personId->equals($relatedPersonId)) {
            throw new \DomainException('A person cannot have a relationship with themselves');
        }

        // Get both persons to check ages
        $person = $this->personRepository->findById($personId);
        $relatedPerson = $this->personRepository->findById($relatedPersonId);

        if (!$person || !$relatedPerson) {
            throw new \DomainException('One or both persons not found');
        }

        // Get ages (may be null if not set)
        $personAge = $person->getAge();
        $relatedPersonAge = $relatedPerson->getAge();

        // Only validate age-based rules if both ages are known
        if ($personAge !== null && $relatedPersonAge !== null) {
            $ageDifference = $personAge - $relatedPersonAge;

            // Validate parent-child relationships
            if ($type->isParentChildRelationship()) {
                $minParentAge = 12; // Minimum biological age for parenthood
                
                if ($type->isParentType()) {
                    // Person is the parent, related person is the child
                    // Parent must be older than child
                    if ($ageDifference < $minParentAge) {
                        throw new \DomainException(
                            "A parent must be at least {$minParentAge} years older than their child. " .
                            "Current age difference: {$ageDifference} years."
                        );
                    }
                } else {
                    // Person is the child, related person is the parent
                    // Parent (related person) must be older than child (person)
                    if ($ageDifference > -$minParentAge) {
                        throw new \DomainException(
                            "A parent must be at least {$minParentAge} years older than their child. " .
                            "Current age difference: " . abs($ageDifference) . " years."
                        );
                    }
                }
            }

            // Validate grandparent-grandchild relationships
            if ($type->isGrandparentRelationship()) {
                $minGrandparentAge = 24; // Minimum age difference for grandparent
                
                if ($type->isGrandparentType()) {
                    // Person is the grandparent
                    if ($ageDifference < $minGrandparentAge) {
                        throw new \DomainException(
                            "A grandparent must be at least {$minGrandparentAge} years older than their grandchild. " .
                            "Current age difference: {$ageDifference} years."
                        );
                    }
                } else {
                    // Person is the grandchild
                    if ($ageDifference > -$minGrandparentAge) {
                        throw new \DomainException(
                            "A grandparent must be at least {$minGrandparentAge} years older than their grandchild. " .
                            "Current age difference: " . abs($ageDifference) . " years."
                        );
                    }
                }
            }

            // Validate spouse relationships (should be adults)
            if ($type->isSpouseType()) {
                $minMarriageAge = 16; // Minimum age for marriage
                if ($personAge < $minMarriageAge || $relatedPersonAge < $minMarriageAge) {
                    throw new \DomainException(
                        "Both persons must be at least {$minMarriageAge} years old for a spouse relationship."
                    );
                }
            }
        }

        // Check for circular relationships (prevent someone being their own ancestor)
        if ($this->wouldCreateCircularRelationship($personId, $relatedPersonId, $type)) {
            throw new \DomainException('This relationship would create a circular family tree');
        }

        return true;
    }

    /**
     * Check if adding this relationship would create a circular dependency
     */
    private function wouldCreateCircularRelationship(
        PersonId $personId,
        PersonId $relatedPersonId,
        RelationshipType $type
    ): bool {
        // For now, just prevent direct circular relationships
        // More complex circular detection can be added later
        
        // Check if the inverse relationship already exists
        $existingRelationship = $this->relationshipRepository->findRelationship(
            $relatedPersonId,
            $personId
        );

        if ($existingRelationship) {
            $inverseType = $type->getInverse();
            // If the existing relationship is not the expected inverse, it's circular
            if ($existingRelationship->getRelationshipType()->toString() !== $inverseType->toString()) {
                return true;
            }
        }

        return false;
    }
}
