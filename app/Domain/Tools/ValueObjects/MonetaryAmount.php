<?php

namespace App\Domain\Tools\ValueObjects;

use InvalidArgumentException;

final class MonetaryAmount
{
    private function __construct(private readonly float $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function fromFloat(float $amount): self
    {
        return new self($amount);
    }

    public function value(): float
    {
        return $this->amount;
    }

    public function add(MonetaryAmount $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function subtract(MonetaryAmount $other): self
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

    public function isGreaterThan(MonetaryAmount $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function formatted(): string
    {
        return 'K' . number_format($this->amount, 2);
    }
}
