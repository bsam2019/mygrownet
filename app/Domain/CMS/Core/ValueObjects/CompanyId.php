<?php

namespace App\Domain\CMS\Core\ValueObjects;

use InvalidArgumentException;

final class CompanyId
{
    private function __construct(
        private readonly int $value
    ) {
        if ($value <= 0) {
            throw new InvalidArgumentException('Company ID must be a positive integer');
        }
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(CompanyId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
