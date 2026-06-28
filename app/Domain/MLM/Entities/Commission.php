<?php

declare(strict_types=1);

namespace App\Domain\MLM\Entities;

use App\Domain\MLM\ValueObjects\CommissionId;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\CommissionLevel;
use App\Domain\MLM\ValueObjects\CommissionAmount;
use App\Domain\MLM\ValueObjects\CommissionType;
use App\Domain\MLM\ValueObjects\CommissionStatus;
use DateTimeImmutable;

class Commission
{
    private function __construct(
        private CommissionId $id,
        private UserId $earnerId,
        private UserId $sourceId,
        private CommissionLevel $level,
        private CommissionAmount $amount,
        private CommissionType $type,
        private CommissionStatus $status,
        private DateTimeImmutable $earnedAt,
        private ?DateTimeImmutable $paidAt = null
    ) {}

    public static function create(
        CommissionId $id,
        UserId $earnerId,
        UserId $sourceId,
        CommissionLevel $level,
        CommissionAmount $amount,
        CommissionType $type
    ): self {
        return new self(
            $id,
            $earnerId,
            $sourceId,
            $level,
            $amount,
            $type,
            CommissionStatus::pending(),
            new DateTimeImmutable()
        );
    }

    public static function fromPersistence(
        CommissionId $id,
        UserId $earnerId,
        UserId $sourceId,
        CommissionLevel $level,
        CommissionAmount $amount,
        CommissionType $type,
        CommissionStatus $status,
        DateTimeImmutable $earnedAt,
        ?DateTimeImmutable $paidAt = null
    ): self {
        return new self(
            $id,
            $earnerId,
            $sourceId,
            $level,
            $amount,
            $type,
            $status,
            $earnedAt,
            $paidAt
        );
    }

    public function calculateAmount(float $packageAmount, CommissionLevel $level): CommissionAmount
    {
        $rates = [
            1 => 12.0, // Level 1: 12%
            2 => 6.0,  // Level 2: 6%
            3 => 4.0,  // Level 3: 4%
            4 => 2.0,  // Level 4: 2%
            5 => 1.0,  // Level 5: 1%
        ];

        $rate = $rates[$level->value()] ?? 0.0;
        $commissionAmount = $packageAmount * ($rate / 100);

        return CommissionAmount::fromFloat($commissionAmount);
    }

    public function calculatePerformanceBonus(float $teamVolume, float $bonusRate): CommissionAmount
    {
        $bonusAmount = $teamVolume * ($bonusRate / 100);
        return CommissionAmount::fromFloat($bonusAmount);
    }

    public function calculateTeamVolumeBonus(float $teamVolume): CommissionAmount
    {
        // MyGrowNet team volume bonus thresholds
        $thresholds = [
            100000 => 10.0, // K100,000+ = 10%
            50000 => 7.0,   // K50,000+ = 7%
            25000 => 5.0,   // K25,000+ = 5%
            10000 => 2.0,   // K10,000+ = 2%
        ];

        foreach ($thresholds as $threshold => $rate) {
            if ($teamVolume >= $threshold) {
                return $this->calculatePerformanceBonus($teamVolume, $rate);
            }
        }

        return CommissionAmount::fromFloat(0);
    }

    public function calculateLeadershipBonus(float $teamVolume, string $leadershipLevel): CommissionAmount
    {
        $leadershipRates = [
            'elite_leader' => 3.0,
            'diamond_leader' => 2.5,
            'gold_leader' => 2.0,
            'developing_leader' => 1.0
        ];

        $rate = $leadershipRates[$leadershipLevel] ?? 0.0;
        return $this->calculatePerformanceBonus($teamVolume, $rate);
    }

    public function isEligibleForPayment(): bool
    {
        return $this->status->isPending() && $this->amount->isPositive();
    }

    public function markAsPaid(): void
    {
        if (!$this->status->isPending()) {
            throw new \DomainException('Commission is not in pending status');
        }

        $this->status = CommissionStatus::paid();
        $this->paidAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if ($this->status->isPaid()) {
            throw new \DomainException('Cannot cancel a paid commission');
        }

        $this->status = CommissionStatus::cancelled();
    }

    // Getters
    public function getId(): CommissionId
    {
        return $this->id;
    }

    public function getEarnerId(): UserId
    {
        return $this->earnerId;
    }

    public function getSourceId(): UserId
    {
        return $this->sourceId;
    }

    public function getLevel(): CommissionLevel
    {
        return $this->level;
    }

    public function getAmount(): CommissionAmount
    {
        return $this->amount;
    }

    public function getType(): CommissionType
    {
        return $this->type;
    }

    public function getStatus(): CommissionStatus
    {
        return $this->status;
    }

    public function getEarnedAt(): DateTimeImmutable
    {
        return $this->earnedAt;
    }

    public function getPaidAt(): ?DateTimeImmutable
    {
        return $this->paidAt;
    }
}