<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Entities;

use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\NetworkLevel;
use DateTimeImmutable;

class Referral
{
    public function __construct(
        private int $id,
        private MemberId $referrerId,
        private MemberId $referredMemberId,
        private string $referredName,
        private string $referredEmail,
        private NetworkLevel $level,
        private DateTimeImmutable $createdAt,
        private ?string $tier = null,
        private ?bool $hasStarterKit = false,
        private ?string $starterKitTier = null,
        private bool $isActive = false,
        private float $personalVolume = 0,
    ) {}

    public function id(): int { return $this->id; }
    public function referrerId(): MemberId { return $this->referrerId; }
    public function referredMemberId(): MemberId { return $this->referredMemberId; }
    public function referredName(): string { return $this->referredName; }
    public function referredEmail(): string { return $this->referredEmail; }
    public function level(): NetworkLevel { return $this->level; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function tier(): ?string { return $this->tier; }
    public function isActive(): bool { return $this->isActive; }
    public function personalVolume(): float { return $this->personalVolume; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'referrer_id' => $this->referrerId->value(),
            'referred_member_id' => $this->referredMemberId->value(),
            'name' => $this->referredName,
            'email' => $this->referredEmail,
            'level' => $this->level->value(),
            'tier' => $this->tier,
            'has_starter_kit' => $this->hasStarterKit,
            'starter_kit_tier' => $this->starterKitTier,
            'is_active' => $this->isActive,
            'personal_volume' => $this->personalVolume,
            'joined_date' => $this->createdAt->format('M d, Y'),
        ];
    }
}
