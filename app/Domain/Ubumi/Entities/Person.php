<?php

namespace App\Domain\Ubumi\Entities;

use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\PersonName;
use App\Domain\Ubumi\ValueObjects\ApproximateAge;
use App\Domain\Ubumi\ValueObjects\Slug;
use DateTimeImmutable;

/**
 * Person Entity
 * 
 * Represents a family member with flexible data entry
 */
class Person
{
    private PersonId $id;
    private string $familyId;
    private PersonName $name;
    private Slug $slug;
    private ?string $photoUrl;
    private ?DateTimeImmutable $dateOfBirth;
    private ?ApproximateAge $approximateAge;
    private ?string $gender;
    private bool $isDeceased;
    private bool $isMerged;
    private ?array $mergedFrom;
    private int $createdBy;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;
    private ?DateTimeImmutable $deletedAt;

    private function __construct(
        PersonId $id,
        string $familyId,
        PersonName $name,
        Slug $slug,
        ?string $photoUrl,
        ?DateTimeImmutable $dateOfBirth,
        ?ApproximateAge $approximateAge,
        ?string $gender,
        bool $isDeceased,
        bool $isMerged,
        ?array $mergedFrom,
        int $createdBy,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
        ?DateTimeImmutable $deletedAt
    ) {
        $this->id = $id;
        $this->familyId = $familyId;
        $this->name = $name;
        $this->slug = $slug;
        $this->photoUrl = $photoUrl;
        $this->dateOfBirth = $dateOfBirth;
        $this->approximateAge = $approximateAge;
        $this->gender = $gender;
        $this->isDeceased = $isDeceased;
        $this->isMerged = $isMerged;
        $this->mergedFrom = $mergedFrom;
        $this->createdBy = $createdBy;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }

    public static function create(
        string $familyId,
        PersonName $name,
        Slug $slug,
        int $createdBy,
        ?string $photoUrl = null,
        ?DateTimeImmutable $dateOfBirth = null,
        ?ApproximateAge $approximateAge = null,
        ?string $gender = null
    ): self {
        return new self(
            PersonId::generate(),
            $familyId,
            $name,
            $slug,
            $photoUrl,
            $dateOfBirth,
            $approximateAge,
            $gender,
            false, // isDeceased
            false, // isMerged
            null,  // mergedFrom
            $createdBy,
            new DateTimeImmutable(),
            null,
            null
        );
    }

    public static function reconstitute(
        PersonId $id,
        string $familyId,
        PersonName $name,
        Slug $slug,
        ?string $photoUrl,
        ?DateTimeImmutable $dateOfBirth,
        ?ApproximateAge $approximateAge,
        ?string $gender,
        bool $isDeceased,
        bool $isMerged,
        ?array $mergedFrom,
        int $createdBy,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
        ?DateTimeImmutable $deletedAt
    ): self {
        return new self(
            $id,
            $familyId,
            $name,
            $slug,
            $photoUrl,
            $dateOfBirth,
            $approximateAge,
            $gender,
            $isDeceased,
            $isMerged,
            $mergedFrom,
            $createdBy,
            $createdAt,
            $updatedAt,
            $deletedAt
        );
    }

    public function updateProfile(
        PersonName $name,
        Slug $slug,
        ?string $photoUrl = null,
        ?DateTimeImmutable $dateOfBirth = null,
        ?ApproximateAge $approximateAge = null,
        ?string $gender = null
    ): void {
        $this->name = $name;
        $this->slug = $slug;
        $this->photoUrl = $photoUrl;
        $this->dateOfBirth = $dateOfBirth;
        $this->approximateAge = $approximateAge;
        $this->gender = $gender;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsDeceased(): void
    {
        $this->isDeceased = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsMerged(array $originalPersonIds): void
    {
        $this->isMerged = true;
        $this->mergedFrom = $originalPersonIds;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function softDelete(): void
    {
        $this->deletedAt = new DateTimeImmutable();
    }

    public function restore(): void
    {
        $this->deletedAt = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getAge(): ?int
    {
        if ($this->dateOfBirth) {
            $now = new DateTimeImmutable();
            return $now->diff($this->dateOfBirth)->y;
        }

        if ($this->approximateAge) {
            return $this->approximateAge->getValue();
        }

        return null;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    // Getters
    public function getId(): PersonId
    {
        return $this->id;
    }

    public function getFamilyId(): string
    {
        return $this->familyId;
    }

    public function getName(): PersonName
    {
        return $this->name;
    }

    public function getSlug(): Slug
    {
        return $this->slug;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function getDateOfBirth(): ?DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function getApproximateAge(): ?ApproximateAge
    {
        return $this->approximateAge;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getIsDeceased(): bool
    {
        return $this->isDeceased;
    }

    public function getIsMerged(): bool
    {
        return $this->isMerged;
    }

    public function getMergedFrom(): ?array
    {
        return $this->mergedFrom;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
