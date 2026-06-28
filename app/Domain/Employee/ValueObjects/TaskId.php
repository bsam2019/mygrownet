<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObjects;

use Ramsey\Uuid\Uuid;

final class TaskId
{
    private function __construct(private readonly string $value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException('Task ID cannot be empty');
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function fromInt(int $value): self
    {
        return new self((string) $value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function toInt(): int
    {
        return (int) $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
