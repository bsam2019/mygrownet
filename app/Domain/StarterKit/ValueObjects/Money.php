<?php

namespace App\Domain\StarterKit\ValueObjects;

use InvalidArgumentException;

final class Money
{
    private function __construct(
        private readonly int $amount, // Amount in smallest currency unit (ngwee)
        private readonly string $currency = 'ZMW'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function fromKwacha(int $kwacha): self
    {
        return new self($kwacha * 100); // Convert to ngwee
    }

    public static function fromNgwee(int $ngwee): self
    {
        return new self($ngwee);
    }

    public function toKwacha(): int
    {
        return (int) ($this->amount / 100);
    }

    public function toNgwee(): int
    {
        return $this->amount;
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        
        if ($this->amount < $other->amount) {
            throw new InvalidArgumentException('Cannot subtract to negative amount');
        }
        
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount > $other->amount;
    }

    public function isLessThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount < $other->amount;
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount 
            && $this->currency === $other->currency;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot operate on different currencies');
        }
    }

    public function __toString(): string
    {
        return "K" . number_format($this->toKwacha(), 2);
    }
}
