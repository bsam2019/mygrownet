<?php

namespace App\Domain\Announcement\Entities;

use App\Domain\Announcement\ValueObjects\AnnouncementType;
use App\Domain\Announcement\ValueObjects\TargetAudience;
use DateTimeImmutable;

/**
 * Announcement Domain Entity
 * 
 * Represents a system-wide announcement that can be targeted to specific user groups
 */
class Announcement
{
    private function __construct(
        private int $id,
        private string $title,
        private string $message,
        private AnnouncementType $type,
        private TargetAudience $targetAudience,
        private ?array $tierFilter,
        private bool $isActive,
        private ?DateTimeImmutable $startsAt,
        private ?DateTimeImmutable $expiresAt,
        private int $createdBy,
        private DateTimeImmutable $createdAt
    ) {}

    public static function create(
        int $id,
        string $title,
        string $message,
        AnnouncementType $type,
        TargetAudience $targetAudience,
        ?array $tierFilter,
        bool $isActive,
        ?DateTimeImmutable $startsAt,
        ?DateTimeImmutable $expiresAt,
        int $createdBy,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            $id,
            $title,
            $message,
            $type,
            $targetAudience,
            $tierFilter,
            $isActive,
            $startsAt,
            $expiresAt,
            $createdBy,
            $createdAt
        );
    }

    public function isVisibleToUser(string $userTier, DateTimeImmutable $now): bool
    {
        // Check if active
        if (!$this->isActive) {
            return false;
        }

        // Check start date
        if ($this->startsAt && $now < $this->startsAt) {
            return false;
        }

        // Check expiry date
        if ($this->expiresAt && $now > $this->expiresAt) {
            return false;
        }

        // Check tier targeting
        if ($this->targetAudience->isTierSpecific() && $this->tierFilter) {
            return in_array($userTier, $this->tierFilter);
        }

        return true;
    }

    public function isUrgent(): bool
    {
        return $this->type->isUrgent();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getMessage(): string { return $this->message; }
    public function getType(): AnnouncementType { return $this->type; }
    public function getTargetAudience(): TargetAudience { return $this->targetAudience; }
    public function getTierFilter(): ?array { return $this->tierFilter; }
    public function isActive(): bool { return $this->isActive; }
    public function getStartsAt(): ?DateTimeImmutable { return $this->startsAt; }
    public function getExpiresAt(): ?DateTimeImmutable { return $this->expiresAt; }
    public function getCreatedBy(): int { return $this->createdBy; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
}
