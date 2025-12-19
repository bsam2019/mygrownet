<?php

namespace App\Domain\Marketplace\ValueObjects;

use InvalidArgumentException;

final class Money
{
    private function __construct(
        private readonly int $amount,
        private readonly string $currency = 'ZMW'
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function fromKwacha(float $amount): self
    {
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

    public function add(Money $other): self
    {
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        return new self(max(0, $this->amount - $other->amount), $this->currency);
    }

    public function multiply(int $factor): self
    {
        return new self($this->amount * $factor, $this->currency);
    }

    public function format(): string
    {
        return 'K' . number_format($this->toKwacha(), 2);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }
}
