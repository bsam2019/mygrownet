<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\ValueObjects;

class Money
{
    public function __construct(
        private float $amount,
        private string $currency = 'ZMW'
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
    }

    public function amount(): float
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot add different currencies');
        }
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot subtract different currencies');
        }
        if ($other->amount > $this->amount) {
            throw new \InvalidArgumentException('Insufficient funds');
        }
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        return new self($this->amount * $multiplier, $this->currency);
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function isLessThan(self $other): bool
    {
        return $this->amount < $other->amount;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }
}
