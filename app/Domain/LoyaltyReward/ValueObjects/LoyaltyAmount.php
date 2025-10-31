<?php

namespace App\Domain\LoyaltyReward\ValueObjects;

class LoyaltyAmount
{
    private const MAX_CASH_CONVERSION_PERCENTAGE = 40;
    private const MIN_CASH_CONVERSION = 100;

    private function __construct(private int $amount)
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Loyalty amount cannot be negative');
        }
    }

    public static function fromKwacha(int $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function add(LoyaltyAmount $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function subtract(LoyaltyAmount $other): self
    {
        $result = $this->amount - $other->amount;
        if ($result < 0) {
            throw new \DomainException('Cannot subtract more than available amount');
        }
        return new self($result);
    }

    public function calculatePercentage(float $percentage): self
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new \InvalidArgumentException('Percentage must be between 0 and 100');
        }
        return new self((int)($this->amount * $percentage / 100));
    }

    public function getMaxCashConversion(): self
    {
        return $this->calculatePercentage(self::MAX_CASH_CONVERSION_PERCENTAGE);
    }

    public function canConvertToCash(): bool
    {
        return $this->amount >= self::MIN_CASH_CONVERSION;
    }

    public function isGreaterThan(LoyaltyAmount $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function isGreaterThanOrEqual(LoyaltyAmount $other): bool
    {
        return $this->amount >= $other->amount;
    }

    public function isLessThan(LoyaltyAmount $other): bool
    {
        return $this->amount < $other->amount;
    }

    public function isLessThanOrEqual(LoyaltyAmount $other): bool
    {
        return $this->amount <= $other->amount;
    }

    public function equals(LoyaltyAmount $other): bool
    {
        return $this->amount === $other->amount;
    }

    public function toKwacha(): int
    {
        return $this->amount;
    }

    public function toFloat(): float
    {
        return (float)$this->amount;
    }

    public function format(): string
    {
        return 'K' . number_format($this->amount, 2);
    }
}
