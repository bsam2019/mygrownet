<?php

declare(strict_types=1);

namespace App\Domain\MLM\Entities;

use App\Domain\MLM\ValueObjects\CommissionId;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\CommissionAmount;
use App\Domain\MLM\ValueObjects\PerformanceBonusType;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;
use App\Domain\MLM\ValueObjects\CommissionStatus;
use DateTimeImmutable;

class PerformanceBonus
{
    private function __construct(
        private CommissionId $id,
        private UserId $userId,
        private PerformanceBonusType $bonusType,
        private CommissionAmount $amount,
        private TeamVolumeAmount $teamVolume,
        private float $bonusRate,
        private CommissionStatus $status,
        private DateTimeImmutable $earnedAt,
        private ?DateTimeImmutable $paidAt = null,
        private ?string $leadershipLevel = null
    ) {}

    public static function createTeamVolumeBonus(
        CommissionId $id,
        UserId $userId,
        TeamVolumeAmount $teamVolume
    ): self {
        $bonusRate = $teamVolume->getPerformanceBonusRate();
        $amount = $teamVolume->calculatePerformanceBonus();

        return new self(
            $id,
            $userId,
            PerformanceBonusType::teamVolume(),
            $amount,
            $teamVolume,
            $bonusRate,
            CommissionStatus::pending(),
            new DateTimeImmutable()
        );
    }

    public static function createLeadershipBonus(
        CommissionId $id,
        UserId $userId,
        TeamVolumeAmount $teamVolume,
        string $leadershipLevel,
        float $bonusRate
    ): self {
        $amount = CommissionAmount::fromFloat($teamVolume->value() * ($bonusRate / 100));

        return new self(
            $id,
            $userId,
            PerformanceBonusType::leadership(),
            $amount,
            $teamVolume,
            $bonusRate,
            CommissionStatus::pending(),
            new DateTimeImmutable(),
            null,
            $leadershipLevel
        );
    }

    public static function createProfitBoostBonus(
        CommissionId $id,
        UserId $userId,
        CommissionAmount $baseCommission
    ): self {
        $boostAmount = CommissionAmount::fromFloat($baseCommission->value() * 0.25); // 25% boost
        $teamVolume = TeamVolumeAmount::zero(); // Not applicable for profit boost

        return new self(
            $id,
            $userId,
            PerformanceBonusType::profitBoost(),
            $boostAmount,
            $teamVolume,
            25.0, // 25% boost rate
            CommissionStatus::pending(),
            new DateTimeImmutable()
        );
    }

    public function isEligibleForPayment(): bool
    {
        return $this->status->isPending() && $this->amount->isPositive();
    }

    public function markAsPaid(): void
    {
        if (!$this->status->isPending()) {
            throw new \DomainException('Performance bonus is not in pending status');
        }

        $this->status = CommissionStatus::paid();
        $this->paidAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if ($this->status->isPaid()) {
            throw new \DomainException('Cannot cancel a paid performance bonus');
        }

        $this->status = CommissionStatus::cancelled();
    }

    public function isTeamVolumeBonus(): bool
    {
        return $this->bonusType->isTeamVolume();
    }

    public function isLeadershipBonus(): bool
    {
        return $this->bonusType->isLeadership();
    }

    public function isProfitBoostBonus(): bool
    {
        return $this->bonusType->isProfitBoost();
    }

    public function calculateMonthlyProjection(int $months = 12): CommissionAmount
    {
        if ($this->bonusType->isProfitBoost()) {
            // Profit boost is one-time, not monthly
            return $this->amount;
        }

        // Team volume and leadership bonuses are monthly
        return CommissionAmount::fromFloat($this->amount->value() * $months);
    }

    public function getPerformanceMetrics(): array
    {
        return [
            'bonus_type' => $this->bonusType->value(),
            'team_volume' => $this->teamVolume->value(),
            'bonus_rate' => $this->bonusRate,
            'bonus_amount' => $this->amount->value(),
            'leadership_level' => $this->leadershipLevel,
            'earned_at' => $this->earnedAt->format('Y-m-d H:i:s'),
            'status' => $this->status->value()
        ];
    }

    // Getters
    public function getId(): CommissionId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getBonusType(): PerformanceBonusType
    {
        return $this->bonusType;
    }

    public function getAmount(): CommissionAmount
    {
        return $this->amount;
    }

    public function getTeamVolume(): TeamVolumeAmount
    {
        return $this->teamVolume;
    }

    public function getBonusRate(): float
    {
        return $this->bonusRate;
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

    public function getLeadershipLevel(): ?string
    {
        return $this->leadershipLevel;
    }
}