<?php

namespace App\Domain\Module\ValueObjects;

class ModuleId
{
    private function __construct(private readonly string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('Module ID cannot be empty');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(ModuleId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
