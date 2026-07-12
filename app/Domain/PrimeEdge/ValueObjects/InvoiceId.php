<?php

namespace App\Domain\PrimeEdge\ValueObjects;

use Ramsey\Uuid\Uuid;
use InvalidArgumentException;

final class InvoiceId
{
    private function __construct(private string $value)
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException('Invalid InvoiceId UUID');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
