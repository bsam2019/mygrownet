<?php

namespace App\Domain\Module\ValueObjects;

class Money
{
    private function __construct(
        private readonly int $amount,
        private readonly string $currency = 'ZMW'
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function fromAmount(int $amount, string $currency = 'ZMW'): self
    {
        return new self($amount, $currency);
    }

    public static function zero(string $currency = 'ZMW'): self
    {
        return new self(0, $currency);
    }

    public static function fromCents(int $cents, string $currency = 'ZMW'): self
    {
        return new self((int) ($cents / 100), $currency);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->ensureSameCurrency($other);
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(int $multiplier): self
    {
        return new self($this->amount * $multiplier, $this->currency);
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->ensureSameCurrency($other);
        return $this->amount > $other->amount;
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    private function ensureSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot operate on different currencies');
        }
    }

    public function formatted(): string
    {
        return "K" . number_format($this->amount, 2);
    }
}
