<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\ValueObjects;

use InvalidArgumentException;

final class JourneyId
{
    private function __construct(private readonly int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Journey ID must be non-negative');
        }
    }

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

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
