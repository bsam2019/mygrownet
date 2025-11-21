<?php

namespace App\Domain\Tools\ValueObjects;

use InvalidArgumentException;

class BusinessPlanIdPlanId
{
    private function __construct(private readonly int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Business plan ID must be positive');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(BusinessPlanId $other): bool
    {
        return $this->value === $other->value;
    }
}
value === $other->value;
    }
}
    private functi
{
