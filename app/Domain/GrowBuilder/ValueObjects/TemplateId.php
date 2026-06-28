<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\ValueObjects;

final class TemplateId
{
    private function __construct(private int $value) {}

    public static function fromInt(int $value): self
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Template ID must be positive');
        }
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
