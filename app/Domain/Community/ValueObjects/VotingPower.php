<?php

declare(strict_types=1);

namespace App\Domain\Community\ValueObjects;

use InvalidArgumentException;

final class VotingPower
{
    private function __construct(private float $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Voting power cannot be negative');
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public static function zero(): self
    {
        return new self(0.0);
    }

    public static function calculateFromContribution(
        ContributionAmount $contribution,
        ContributionAmount $totalProjectFunding,
        float $tierMultiplier = 1.0
    ): self {
        if ($totalProjectFunding->isZero()) {
            return self::zero();
        }

        $contributionPercentage = $contribution->calculatePercentageOf($totalProjectFunding);
        $votingPower = $contributionPercentage * $tierMultiplier;

        return new self($votingPower);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function isPositive(): bool
    {
        return $this->value > 0;
    }

    public function isZero(): bool
    {
        return $this->value === 0.0;
    }

    public function add(VotingPower $other): self
    {
        return new self($this->value + $other->value);
    }

    public function multiply(float $multiplier): self
    {
        if ($multiplier < 0) {
            throw new InvalidArgumentException('Multiplier cannot be negative');
        }
        return new self($this->value * $multiplier);
    }

    public function calculatePercentageOf(VotingPower $total): float
    {
        if ($total->isZero()) {
            return 0.0;
        }
        return ($this->value / $total->value) * 100;
    }

    public function equals(VotingPower $other): bool
    {
        return abs($this->value - $other->value) < 0.01; // Account for floating point precision
    }

    public function isGreaterThan(VotingPower $other): bool
    {
        return $this->value > $other->value;
    }

    public function isGreaterThanOrEqual(VotingPower $other): bool
    {
        return $this->value >= $other->value;
    }

    public function toFormattedString(): string
    {
        return number_format($this->value, 2) . '%';
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}