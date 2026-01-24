<?php

namespace App\Domain\Ubumi\ValueObjects;

use Ramsey\Uuid\Uuid;

final class CheckInId
{
    private function __construct(
        private readonly string $value
    ) {}

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid CheckIn ID format');
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(CheckInId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
