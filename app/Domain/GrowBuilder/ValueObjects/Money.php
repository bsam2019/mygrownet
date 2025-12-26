<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\ValueObjects;

final class Money
{
    private const CURRENCY = 'ZMW';
    private const NGWEE_PER_KWACHA = 100;

    private function __construct(private int $amountInNgwee) {}

    public static function fromNgwee(int $amount): self
    {
        return new self($amount);
    }

    public static function fromKwacha(float $amount): self
    {
        return new self((int) round($amount * self::NGWEE_PER_KWACHA));
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function getAmountInNgwee(): int
    {
        return $this->amountInNgwee;
    }

    public function getAmountInKwacha(): float
    {
        return $this->amountInNgwee / self::NGWEE_PER_KWACHA;
    }

    public function format(): string
    {
        return 'K' . number_format($this->getAmountInKwacha(), 2);
    }

    public function formatWithCurrency(): string
    {
        return self::CURRENCY . ' ' . number_format($this->getAmountInKwacha(), 2);
    }

    public function add(self $other): self
    {
        return new self($this->amountInNgwee + $other->amountInNgwee);
    }

    public function subtract(self $other): self
    {
        return new self(max(0, $this->amountInNgwee - $other->amountInNgwee));
    }

    public function multiply(int $factor): self
    {
        return new self($this->amountInNgwee * $factor);
    }

    public function percentage(float $percent): self
    {
        return new self((int) round($this->amountInNgwee * $percent / 100));
    }

    public function isZero(): bool
    {
        return $this->amountInNgwee === 0;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->amountInNgwee > $other->amountInNgwee;
    }

    public function isLessThan(self $other): bool
    {
        return $this->amountInNgwee < $other->amountInNgwee;
    }

    public function equals(self $other): bool
    {
        return $this->amountInNgwee === $other->amountInNgwee;
    }

    public function getCurrency(): string
    {
        return self::CURRENCY;
    }
}
