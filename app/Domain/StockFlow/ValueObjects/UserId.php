<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\ValueObjects;

class UserId
{
    private function __construct(private int $value) {}

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public static function generate(): self
    {
        return new self(0);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
