<?php

namespace App\Domain\LoyaltyReward\Entities;

use App\Domain\LoyaltyReward\ValueObjects\ActivityType;
use DateTimeImmutable;

class LoyaltyActivity
{
    private function __construct(
        private int $id,
        private int $userId,
        private int $cycleId,
        private ActivityType $type,
        private string $description,
        private DateTimeImmutable $performedAt,
        private bool $verified
    ) {}

    public static function record(
        int $userId,
        int $cycleId,
        ActivityType $type,
        string $description
    ): self {
        return new self(
            id: 0, // Will be set by repository
            userId: $userId,
            cycleId: $cycleId,
            type: $type,
            description: $description,
            performedAt: new DateTimeImmutable(),
            verified: false
        );
    }

    public function verify(): void
    {
        $this->verified = true;
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCycleId(): int
    {
        return $this->cycleId;
    }

    public function getType(): ActivityType
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPerformedAt(): DateTimeImmutable
    {
        return $this->performedAt;
    }
}
