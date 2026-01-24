<?php

namespace App\Domain\Ubumi\ValueObjects;

use Ramsey\Uuid\Uuid;

/**
 * Family ID Value Object
 */
final class FamilyId
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException('Invalid Family ID format');
        }
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(FamilyId $other): bool
    {
        return $this->value === $other->value;
    }
}
