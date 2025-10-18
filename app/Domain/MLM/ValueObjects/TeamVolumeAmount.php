<?php

declare(strict_types=1);

namespace App\Domain\MLM\ValueObjects;

class TeamVolumeAmount
{
    private function __construct(private float $amount)
    {
        $this->validate($amount);
    }

    public static function fromFloat(float $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self(0.0);
    }

    public function value(): float
    {
        return $this->amount;
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    public function isZero(): bool
    {
        return $this->amount === 0.0;
    }

    public function add(TeamVolumeAmount $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function subtract(TeamVolumeAmount $other): self
    {
        return new self(max(0, $this->amount - $other->amount));
    }

    public function multiply(float $multiplier): self
    {
        return new self($this->amount * $multiplier);
    }

    public function percentage(float $percentage): self
    {
        return new self($this->amount * ($percentage / 100));
    }

    public function isGreaterThan(TeamVolumeAmount $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function isGreaterThanOrEqual(TeamVolumeAmount $other): bool
    {
        return $this->amount >= $other->amount;
    }

    public function isLessThan(TeamVolumeAmount $other): bool
    {
        return $this->amount < $other->amount;
    }

    public function equals(TeamVolumeAmount $other): bool
    {
        return abs($this->amount - $other->amount) < 0.01; // Allow for floating point precision
    }

    public function meetsThreshold(float $threshold): bool
    {
        return $this->amount >= $threshold;
    }

    public function getPerformanceBonusRate(): float
    {
        // MyGrowNet performance bonus thresholds
        if ($this->amount >= 100000) return 10.0;
        if ($this->amount >= 50000) return 7.0;
        if ($this->amount >= 25000) return 5.0;
        if ($this->amount >= 10000) return 2.0;
        
        return 0.0;
    }

    public function calculatePerformanceBonus(): CommissionAmount
    {
        $rate = $this->getPerformanceBonusRate();
        $bonusAmount = $this->amount * ($rate / 100);
        
        return CommissionAmount::fromFloat($bonusAmount);
    }

    private function validate(float $amount): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Team volume amount cannot be negative');
        }
    }

    public function __toString(): string
    {
        return number_format($this->amount, 2);
    }
}