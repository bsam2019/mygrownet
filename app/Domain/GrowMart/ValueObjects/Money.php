<?php

namespace App\Domain\GrowMart\ValueObjects;

final class Money
{
    private function __construct(private readonly int $ngwee) {}

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

    public function ngwee(): int
    {
        return $this->ngwee;
    }

    public function toKwacha(): float
    {
        return $this->ngwee / 100;
    }

    public function add(self $other): self
    {
        return new self($this->ngwee + $other->ngwee);
    }

    public function subtract(self $other): self
    {
        return new self($this->ngwee - $other->ngwee);
    }

    public function multiply(int $factor): self
    {
        return new self($this->ngwee * $factor);
    }

    public function format(): string
    {
        return 'K' . number_format($this->toKwacha(), 2);
    }

    public function equals(self $other): bool
    {
        return $this->ngwee === $other->ngwee;
    }
}
