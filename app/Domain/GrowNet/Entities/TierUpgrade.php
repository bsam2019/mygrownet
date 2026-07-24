<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Entities;

use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\MembershipTier;
use App\Domain\GrowNet\ValueObjects\Money;
use DateTimeImmutable;

class TierUpgrade
{
    public function __construct(
        private int $id,
        private MemberId $memberId,
        private MembershipTier $fromTier,
        private MembershipTier $toTier,
        private string $reason,
        private Money $achievementBonus,
        private float $teamVolumeAtUpgrade,
        private int $activeReferralsAtUpgrade,
        private DateTimeImmutable $createdAt,
    ) {}

    public static function create(
        MemberId $memberId,
        MembershipTier $fromTier,
        MembershipTier $toTier,
        string $reason = 'manual',
        Money $achievementBonus = new Money(0),
        float $teamVolumeAtUpgrade = 0,
        int $activeReferralsAtUpgrade = 0,
    ): self {
        return new self(
            id: 0,
            memberId: $memberId,
            fromTier: $fromTier,
            toTier: $toTier,
            reason: $reason,
            achievementBonus: $achievementBonus,
            teamVolumeAtUpgrade: $teamVolumeAtUpgrade,
            activeReferralsAtUpgrade: $activeReferralsAtUpgrade,
            createdAt: new DateTimeImmutable(),
        );
    }

    public function id(): int { return $this->id; }
    public function memberId(): MemberId { return $this->memberId; }
    public function fromTier(): MembershipTier { return $this->fromTier; }
    public function toTier(): MembershipTier { return $this->toTier; }
    public function reason(): string { return $this->reason; }
    public function achievementBonus(): Money { return $this->achievementBonus; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->memberId->value(),
            'from_tier' => $this->fromTier->value,
            'to_tier' => $this->toTier->value,
            'reason' => $this->reason,
            'achievement_bonus' => $this->achievementBonus->amount(),
            'team_volume' => $this->teamVolumeAtUpgrade,
            'active_referrals' => $this->activeReferralsAtUpgrade,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
