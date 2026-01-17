<?php

namespace App\Domain\LoyaltyReward\Entities;

use Carbon\Carbon;

class LgrCycle
{
    public const CYCLE_DURATION_DAYS = 70;
    public const DAILY_RATE = 25.00; // Default rate (Basic tier)
    public const MAX_EARNINGS = 1750.00; // 70 days × K25 (Basic tier)
    
    // Tier-based daily rates
    public const TIER_DAILY_RATES = [
        'lite' => 12.50,        // K12.50/day (50% of basic)
        'basic' => 25.00,       // K25/day (baseline)
        'growth_plus' => 37.50, // K37.50/day (150% of basic)
        'pro' => 62.50,         // K62.50/day (250% of basic)
        'premium' => 37.50,     // K37.50/day (legacy, same as growth_plus)
    ];
    
    // Tier-based max earnings (70 days × daily rate)
    public const TIER_MAX_EARNINGS = [
        'lite' => 875.00,       // 70 × K12.50
        'basic' => 1750.00,     // 70 × K25
        'growth_plus' => 2625.00, // 70 × K37.50
        'pro' => 4375.00,       // 70 × K62.50
        'premium' => 2625.00,   // 70 × K37.50 (legacy)
    ];

    private function __construct(
        private ?int $id,
        private int $userId,
        private Carbon $startDate,
        private Carbon $endDate,
        private string $status,
        private int $activeDays,
        private float $totalEarnedLgc,
        private float $dailyRate,
        private ?string $tier = null
    ) {}

    public static function create(int $userId, Carbon $startDate, ?string $tier = 'basic'): self
    {
        $dailyRate = self::TIER_DAILY_RATES[$tier] ?? self::DAILY_RATE;
        
        return new self(
            id: null,
            userId: $userId,
            startDate: $startDate,
            endDate: $startDate->copy()->addDays(self::CYCLE_DURATION_DAYS - 1),
            status: 'active',
            activeDays: 0,
            totalEarnedLgc: 0,
            dailyRate: $dailyRate,
            tier: $tier
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            userId: $data['user_id'],
            startDate: Carbon::parse($data['start_date']),
            endDate: Carbon::parse($data['end_date']),
            status: $data['status'],
            activeDays: $data['active_days'],
            totalEarnedLgc: $data['total_earned_lgc'],
            dailyRate: $data['daily_rate'],
            tier: $data['tier'] ?? null
        );
    }

    public function recordActivity(float $lgcAmount): void
    {
        $this->activeDays++;
        $this->totalEarnedLgc += $lgcAmount;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->endDate->isPast();
    }

    public function complete(): void
    {
        $this->status = 'completed';
    }

    public function suspend(string $reason): void
    {
        $this->status = 'suspended';
    }

    public function canEarnToday(Carbon $date): bool
    {
        $maxEarnings = $this->tier 
            ? (self::TIER_MAX_EARNINGS[$this->tier] ?? self::MAX_EARNINGS)
            : self::MAX_EARNINGS;
            
        return $this->isActive() 
            && $date->between($this->startDate, $this->endDate)
            && $this->totalEarnedLgc < $maxEarnings;
    }

    public function getRemainingDays(): int
    {
        if ($this->isCompleted()) {
            return 0;
        }
        
        return max(0, Carbon::now()->diffInDays($this->endDate, false));
    }

    public function getProjectedEarnings(): float
    {
        $maxEarnings = $this->tier 
            ? (self::TIER_MAX_EARNINGS[$this->tier] ?? self::MAX_EARNINGS)
            : self::MAX_EARNINGS;
            
        $remainingDays = $this->getRemainingDays();
        $potentialEarnings = $this->totalEarnedLgc + ($remainingDays * $this->dailyRate);
        
        return min($potentialEarnings, $maxEarnings);
    }

    public function getCompletionRate(): float
    {
        return ($this->activeDays / self::CYCLE_DURATION_DAYS) * 100;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getStartDate(): Carbon { return $this->startDate; }
    public function getEndDate(): Carbon { return $this->endDate; }
    public function getStatus(): string { return $this->status; }
    public function getActiveDays(): int { return $this->activeDays; }
    public function getTotalEarnedLgc(): float { return $this->totalEarnedLgc; }
    public function getDailyRate(): float { return $this->dailyRate; }
    public function getTier(): ?string { return $this->tier; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'tier' => $this->tier,
            'start_date' => $this->startDate->toDateString(),
            'end_date' => $this->endDate->toDateString(),
            'status' => $this->status,
            'active_days' => $this->activeDays,
            'total_earned_lgc' => $this->totalEarnedLgc,
            'daily_rate' => $this->dailyRate,
            'remaining_days' => $this->getRemainingDays(),
            'projected_earnings' => $this->getProjectedEarnings(),
            'completion_rate' => $this->getCompletionRate(),
        ];
    }
}
