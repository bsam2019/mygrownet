<?php

namespace App\Domain\Library\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final class AccessPeriod
{
    private function __construct(
        private readonly DateTimeImmutable $startsAt,
        private readonly DateTimeImmutable $endsAt
    ) {
        if ($this->endsAt <= $this->startsAt) {
            throw new InvalidArgumentException('End date must be after start date');
        }
    }

    public static function fromDates(DateTimeImmutable $startsAt, DateTimeImmutable $endsAt): self
    {
        return new self($startsAt, $endsAt);
    }

    public static function thirtyDaysFromNow(): self
    {
        $now = new DateTimeImmutable();
        return new self($now, $now->modify('+30 days'));
    }

    public static function fromStartDate(DateTimeImmutable $startsAt, int $days): self
    {
        return new self($startsAt, $startsAt->modify("+{$days} days"));
    }

    public function isActive(): bool
    {
        $now = new DateTimeImmutable();
        return $now >= $this->startsAt && $now <= $this->endsAt;
    }

    public function hasExpired(): bool
    {
        return new DateTimeImmutable() > $this->endsAt;
    }

    public function daysRemaining(): int
    {
        if ($this->hasExpired()) {
            return 0;
        }

        $now = new DateTimeImmutable();
        return $now->diff($this->endsAt)->days;
    }

    public function startsAt(): DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function endsAt(): DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function equals(AccessPeriod $other): bool
    {
        return $this->startsAt == $other->startsAt 
            && $this->endsAt == $other->endsAt;
    }
}
