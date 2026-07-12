<?php

namespace App\Domain\PrimeEdge\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final class DateTimeRange
{
    private function __construct(
        private DateTimeImmutable $start,
        private DateTimeImmutable $end
    ) {
        if ($end <= $start) {
            throw new InvalidArgumentException('End time must be after start time');
        }
    }

    public static function fromDateTimes(DateTimeImmutable $start, DateTimeImmutable $end): self
    {
        return new self($start, $end);
    }

    public function start(): DateTimeImmutable
    {
        return $this->start;
    }

    public function end(): DateTimeImmutable
    {
        return $this->end;
    }

    public function durationMinutes(): int
    {
        return (int) ($this->end->getTimestamp() - $this->start->getTimestamp()) / 60;
    }

    public function overlaps(self $other): bool
    {
        return $this->start < $other->end && $other->start < $this->end;
    }

    public function toArray(): array
    {
        return [
            'start' => $this->start->format('Y-m-d H:i:s'),
            'end' => $this->end->format('Y-m-d H:i:s'),
        ];
    }
}
