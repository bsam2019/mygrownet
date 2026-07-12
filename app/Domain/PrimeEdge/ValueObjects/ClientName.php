<?php

namespace App\Domain\PrimeEdge\ValueObjects;

use InvalidArgumentException;

final class ClientName
{
    private function __construct(private string $value)
    {
        $trimmed = trim($value);
        if (strlen($trimmed) < 2) {
            throw new InvalidArgumentException('Client name must be at least 2 characters');
        }
        if (strlen($trimmed) > 255) {
            throw new InvalidArgumentException('Client name must not exceed 255 characters');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
