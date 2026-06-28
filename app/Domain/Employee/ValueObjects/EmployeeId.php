<?php

declare(strict_types=1);

namespace App\Domain\Employee\ValueObjects;

final class EmployeeId
{
    private int $value;

    private function __construct(int $value)
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Employee ID must be a positive integer');
        }
        $this->value = $value;
    }

    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    public static function fromString(string $id): self
    {
        if (!is_numeric($id) || (int) $id <= 0) {
            throw new \InvalidArgumentException("Invalid employee ID: {$id}");
        }

        return new self((int) $id);
    }

    public function toInt(): int
    {
        return $this->value;
    }

    public function toString(): string
    {
        return (string) $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function equals(EmployeeId $other): bool
    {
        return $this->value === $other->value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}