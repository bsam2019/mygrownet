<?php

namespace App\Domain\Ubumi\ValueObjects;

/**
 * Person Name Value Object
 */
final class PersonName
{
    private string $value;

    private function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    private function validate(string $value): void
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('Person name cannot be empty');
        }

        if (strlen($value) > 255) {
            throw new \InvalidArgumentException('Person name cannot exceed 255 characters');
        }
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(PersonName $other): bool
    {
        return $this->value === $other->value;
    }
}
