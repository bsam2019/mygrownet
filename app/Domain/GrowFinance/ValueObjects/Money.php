<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\ValueObjects;

use InvalidArgumentException;

final class Money
{
    private function __construct(
        private readonly int $amount, // Amount in smallest unit (ngwee)
        private readonly string $currency = 'ZMW'
    ) {}

    public static function fromKwacha(float|int|string $amount): self
    {
        $amount = (float) $amount;
        return new self((int) round($amount * 100));
    }

    public static function fromNgwee(int $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function toKwacha(): float
    {
        return $this->amount / 100;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function format(): string
    {
        return 'K' . number_format($this->toKwacha(), 2);
    }

    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float $multiplier): self
    {
        return new self((int) round($this->amount * $multiplier), $this->currency);
    }

    public function percentage(float $percent): self
    {
        return $this->multiply($percent / 100);
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function greaterThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amount > $other->amount;
    }

    public function lessThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amount < $other->amount;
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot operate on different currencies');
        }
    }
}
