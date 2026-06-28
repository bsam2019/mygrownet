<?php

namespace App\Domain\StarterKit\ValueObjects;

use DateTimeImmutable;
use InvalidArgumentException;

final class ShopCredit
{
    private function __construct(
        private readonly Money $amount,
        private readonly DateTimeImmutable $expiresAt
    ) {}

    public static function create(Money $amount, int $validityDays = 90): self
    {
        if ($validityDays <= 0) {
            throw new InvalidArgumentException('Validity days must be positive');
        }

        $expiresAt = (new DateTimeImmutable())->modify("+{$validityDays} days");
        
        return new self($amount, $expiresAt);
    }

    public static function fromData(Money $amount, DateTimeImmutable $expiresAt): self
    {
        return new self($amount, $expiresAt);
    }

    public function amount(): Money
    {
        return $this->amount;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpired(): bool
    {
        return new DateTimeImmutable() > $this->expiresAt;
    }

    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        $now = new DateTimeImmutable();
        return $now->diff($this->expiresAt)->days;
    }
}
