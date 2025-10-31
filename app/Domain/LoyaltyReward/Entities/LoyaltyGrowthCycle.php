<?php

namespace App\Domain\LoyaltyReward\Entities;

use App\Domain\LoyaltyReward\ValueObjects\CycleId;
use App\Domain\LoyaltyReward\ValueObjects\CycleStatus;
use App\Domain\LoyaltyReward\ValueObjects\LoyaltyAmount;
use DateTimeImmutable;

class LoyaltyGrowthCycle
{
    private const CYCLE_DURATION_DAYS = 70;
    private const DAILY_RATE = 25; // K25 per active day
    private const MAX_TOTAL = 1750; // K1,750 maximum

    private function __construct(
        private CycleId $id,
        private int $userId,
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate,
        private CycleStatus $status,
        private int $activeDays,
        private LoyaltyAmount $earnedAmount,
        private ?DateTimeImmutable $completedAt = null
    ) {}

    public static function start(int $userId, DateTimeImmutable $startDate): self
    {
        $endDate = $startDate->modify('+' . self::CYCLE_DURATION_DAYS . ' days');

        return new self(
            id: CycleId::generate(),
            userId: $userId,
            startDate: $startDate,
            endDate: $endDate,
            status: CycleStatus::active(),
            activeDays: 0,
            earnedAmount: LoyaltyAmount::zero()
        );
    }

    public function recordActiveDay(): void
    {
        if (!$this->status->isActive()) {
            throw new \DomainException('Cannot record activity for inactive cycle');
        }

        if ($this->isExpired()) {
            throw new \DomainException('Cycle has expired');
        }

        if ($this->activeDays >= self::CYCLE_DURATION_DAYS) {
            throw new \DomainException('Maximum active days reached');
        }

        $this->activeDays++;
        $this->earnedAmount = $this->earnedAmount->add(
            LoyaltyAmount::fromKwacha(self::DAILY_RATE)
        );
    }

    public function complete(): void
    {
        if (!$this->status->isActive()) {
            throw new \DomainException('Only active cycles can be completed');
        }

        $this->status = CycleStatus::completed();
        $this->completedAt = new DateTimeImmutable();
    }

    public function suspend(string $reason): void
    {
        $this->status = CycleStatus::suspended();
    }

    public function terminate(string $reason): void
    {
        $this->status = CycleStatus::terminated();
    }

    public function isExpired(): bool
    {
        return new DateTimeImmutable() > $this->endDate;
    }

    public function canRecordActivity(): bool
    {
        return $this->status->isActive() && !$this->isExpired();
    }

    public function getRemainingDays(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        $now = new DateTimeImmutable();
        $diff = $now->diff($this->endDate);
        return $diff->days;
    }

    public function getProgressPercentage(): float
    {
        return ($this->activeDays / self::CYCLE_DURATION_DAYS) * 100;
    }

    // Getters
    public function getId(): CycleId
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getStatus(): CycleStatus
    {
        return $this->status;
    }

    public function getActiveDays(): int
    {
        return $this->activeDays;
    }

    public function getEarnedAmount(): LoyaltyAmount
    {
        return $this->earnedAmount;
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public static function getCycleDurationDays(): int
    {
        return self::CYCLE_DURATION_DAYS;
    }

    public static function getDailyRate(): int
    {
        return self::DAILY_RATE;
    }

    public static function getMaxTotal(): int
    {
        return self::MAX_TOTAL;
    }
}
