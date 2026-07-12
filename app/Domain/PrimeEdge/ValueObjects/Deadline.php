<?php

namespace App\Domain\PrimeEdge\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final class Deadline
{
    private function __construct(private DateTimeImmutable $value) {}

    public static function fromDateTime(DateTimeImmutable $value): self
    {
        return new self($value);
    }

    public static function fromString(string $value): self
    {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        if (!$dt) {
            throw new InvalidArgumentException('Invalid deadline date format (expected Y-m-d)');
        }
        return new self($dt);
    }

    public function toDateTime(): DateTimeImmutable
    {
        return $this->value;
    }

    public function isOverdue(): bool
    {
        return $this->value < new DateTimeImmutable();
    }

    public function daysUntil(): int
    {
        $now = new DateTimeImmutable();
        return (int) $now->diff($this->value)->format('%r%a');
    }

    public function isWithinDays(int $days): bool
    {
        $threshold = new DateTimeImmutable("+{$days} days");
        return $this->value <= $threshold && $this->value >= new DateTimeImmutable();
    }

    public function toString(): string
    {
        return $this->value->format('Y-m-d');
    }

    public function equals(self $other): bool
    {
        return $this->value->format('Y-m-d') === $other->value->format('Y-m-d');
    }
}
