<?php

namespace App\Domain\Support\ValueObjects;

class TicketId
{
    private function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Ticket ID must be positive');
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

    public function equals(TicketId $other): bool
    {
        return $this->value === $other->value;
    }
}
