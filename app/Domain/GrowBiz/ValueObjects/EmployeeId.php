<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\ValueObjects;

use InvalidArgumentException;

final class EmployeeId
{
    private function __construct(private int $value)
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Employee ID must be non-negative');
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

    public function toString(): string
    {
        return (string) $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
