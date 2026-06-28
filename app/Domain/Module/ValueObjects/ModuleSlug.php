<?php

namespace App\Domain\Module\ValueObjects;

class ModuleSlug
{
    private function __construct(private readonly string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('Module slug cannot be empty');
        }

        if (!preg_match('/^[a-z0-9-]+$/', $value)) {
            throw new \InvalidArgumentException('Module slug must contain only lowercase letters, numbers, and hyphens');
        }
    }

    public static function fromString(string $value): self
    {
        return new self(strtolower(trim($value)));
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
