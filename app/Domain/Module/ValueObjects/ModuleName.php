<?php

namespace App\Domain\Module\ValueObjects;

class ModuleName
{
    private function __construct(private readonly string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('Module name cannot be empty');
        }

        if (strlen($value) > 100) {
            throw new \InvalidArgumentException('Module name cannot exceed 100 characters');
        }
    }

    public static function fromString(string $value): self
    {
        return new self(trim($value));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
