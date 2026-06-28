<?php

namespace App\Domain\LoyaltyReward\ValueObjects;

use Ramsey\Uuid\Uuid;

class CycleId
{
    private function __construct(private string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('Cycle ID cannot be empty');
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(CycleId $other): bool
    {
        return $this->value === $other->value;
    }
}
