<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\ValueObjects;

class MemberId
{
    public function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Member ID must be a positive integer');
        }
    }

    public function value(): int
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
