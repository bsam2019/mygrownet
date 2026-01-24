<?php

namespace App\Domain\Ubumi\Entities;

use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\RelationshipType;
use DateTimeImmutable;

/**
 * Relationship Entity
 * 
 * Represents a relationship between two persons in a family
 */
class Relationship
{
    private int $id;
    private PersonId $personId;
    private PersonId $relatedPersonId;
    private RelationshipType $relationshipType;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;

    private function __construct(
        int $id,
        PersonId $personId,
        PersonId $relatedPersonId,
        RelationshipType $relationshipType,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt
    ) {
        $this->id = $id;
        $this->personId = $personId;
        $this->relatedPersonId = $relatedPersonId;
        $this->relationshipType = $relationshipType;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        PersonId $personId,
        PersonId $relatedPersonId,
        RelationshipType $relationshipType
    ): self {
        // Prevent self-relationships
        if ($personId->equals($relatedPersonId)) {
            throw new \DomainException('A person cannot have a relationship with themselves');
        }

        return new self(
            0, // Will be set by repository
            $personId,
            $relatedPersonId,
            $relationshipType,
            new DateTimeImmutable(),
            null
        );
    }

    public static function reconstitute(
        int $id,
        PersonId $personId,
        PersonId $relatedPersonId,
        RelationshipType $relationshipType,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt
    ): self {
        return new self(
            $id,
            $personId,
            $relatedPersonId,
            $relationshipType,
            $createdAt,
            $updatedAt
        );
    }

    public function updateType(RelationshipType $newType): void
    {
        $this->relationshipType = $newType;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getPersonId(): PersonId
    {
        return $this->personId;
    }

    public function getRelatedPersonId(): PersonId
    {
        return $this->relatedPersonId;
    }

    public function getRelationshipType(): RelationshipType
    {
        return $this->relationshipType;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
