<?php

declare(strict_types=1);

namespace App\Domain\Community\ValueObjects;

use InvalidArgumentException;

final class ProjectId
{
    private function __construct(private int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Project ID must be a positive integer');
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

    public function equals(ProjectId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}