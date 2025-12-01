<?php

declare(strict_types=1);

namespace App\Domain\SME\ValueObjects;

use InvalidArgumentException;

final class TaskId
{
    private function __construct(private int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Task ID must be non-negative');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public static function generate(): self
    {
        return new self(0); // Will be set by database
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
