<?php

namespace App\Domain\Ubumi\Entities;

use App\Domain\Ubumi\ValueObjects\FamilyId;
use App\Domain\Ubumi\ValueObjects\FamilyName;
use App\Domain\Ubumi\ValueObjects\Slug;
use DateTimeImmutable;

/**
 * Family Aggregate Root
 * 
 * Represents a family unit with members and relationships
 */
class Family
{
    private FamilyId $id;
    private FamilyName $name;
    private Slug $slug;
    private int $adminUserId;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;

    private function __construct(
        FamilyId $id,
        FamilyName $name,
        Slug $slug,
        int $adminUserId,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->adminUserId = $adminUserId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function create(
        FamilyName $name,
        Slug $slug,
        int $adminUserId
    ): self {
        return new self(
            FamilyId::generate(),
            $name,
            $slug,
            $adminUserId,
            new DateTimeImmutable(),
            null
        );
    }

    public static function reconstitute(
        FamilyId $id,
        FamilyName $name,
        Slug $slug,
        int $adminUserId,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt
    ): self {
        return new self($id, $name, $slug, $adminUserId, $createdAt, $updatedAt);
    }

    public function changeName(FamilyName $newName, Slug $newSlug): void
    {
        $this->name = $newName;
        $this->slug = $newSlug;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeAdmin(int $newAdminUserId): void
    {
        $this->adminUserId = $newAdminUserId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isAdmin(int $userId): bool
    {
        return $this->adminUserId === $userId;
    }

    // Getters
    public function getId(): FamilyId
    {
        return $this->id;
    }

    public function getName(): FamilyName
    {
        return $this->name;
    }

    public function getSlug(): Slug
    {
        return $this->slug;
    }

    public function getAdminUserId(): int
    {
        return $this->adminUserId;
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
