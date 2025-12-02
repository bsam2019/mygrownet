<?php

namespace App\Domain\Module\ValueObjects;

class SubscriptionTier
{
    private function __construct(private readonly string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('Subscription tier cannot be empty');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function basic(): self
    {
        return new self('basic');
    }

    public static function pro(): self
    {
        return new self('pro');
    }

    public static function enterprise(): self
    {
        return new self('enterprise');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(SubscriptionTier $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
