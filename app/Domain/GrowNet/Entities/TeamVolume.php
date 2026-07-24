<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Entities;

use App\Domain\GrowNet\ValueObjects\MemberId;
use DateTimeImmutable;

class TeamVolume
{
    public function __construct(
        private int $id,
        private MemberId $memberId,
        private float $personalVolume,
        private float $teamVolume,
        private float $leftLegVolume,
        private float $rightLegVolume,
        private float $totalVolume,
        private int $activeReferralsCount,
        private DateTimeImmutable $periodStart,
        private DateTimeImmutable $periodEnd,
        private DateTimeImmutable $createdAt,
    ) {}

    public function id(): int { return $this->id; }
    public function memberId(): MemberId { return $this->memberId; }
    public function personalVolume(): float { return $this->personalVolume; }
    public function teamVolume(): float { return $this->teamVolume; }
    public function leftLegVolume(): float { return $this->leftLegVolume; }
    public function rightLegVolume(): float { return $this->rightLegVolume; }
    public function totalVolume(): float { return $this->totalVolume; }
    public function activeReferralsCount(): int { return $this->activeReferralsCount; }
    public function periodStart(): DateTimeImmutable { return $this->periodStart; }
    public function periodEnd(): DateTimeImmutable { return $this->periodEnd; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->memberId->value(),
            'personal_volume' => $this->personalVolume,
            'team_volume' => $this->teamVolume,
            'left_leg_volume' => $this->leftLegVolume,
            'right_leg_volume' => $this->rightLegVolume,
            'total_volume' => $this->totalVolume,
            'active_referrals_count' => $this->activeReferralsCount,
            'period_start' => $this->periodStart->format('Y-m-d'),
            'period_end' => $this->periodEnd->format('Y-m-d'),
        ];
    }
}
