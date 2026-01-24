<?php

namespace App\Domain\Ubumi\ValueObjects;

/**
 * Approximate Age Value Object
 * 
 * Used when exact date of birth is unknown
 */
final class ApproximateAge
{
    private int $value;

    private function __construct(int $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    private function validate(int $value): void
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Age cannot be negative');
        }

        if ($value > 150) {
            throw new \InvalidArgumentException('Age cannot exceed 150 years');
        }
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function equals(ApproximateAge $other): bool
    {
        return $this->value === $other->value;
    }
}
