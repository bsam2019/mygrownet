<?php

namespace App\Domain\Events\ValueObjects;

class EventId
{
    private function __construct(private int $value)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Event ID cannot be negative');
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

    public function value(): int
    {
        return $this->value;
    }

    public function equals(EventId $other): bool
    {
        return $this->value === $other->value;
    }
}
