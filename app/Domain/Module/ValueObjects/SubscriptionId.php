<?php

namespace App\Domain\Module\ValueObjects;

class SubscriptionId
{
    private function __construct(private readonly int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Subscription ID must be positive');
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

    public function equals(SubscriptionId $other): bool
    {
        return $this->value === $other->value;
    }
}
