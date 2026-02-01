<?php

namespace App\Domain\Learning\ValueObjects;

class ModuleId
{
    private function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Module ID must be positive');
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

    public function equals(ModuleId $other): bool
    {
        return $this->value === $other->value;
    }
}
