<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\ValueObjects;

class ReferralCode
{
    public function __construct(private string $code)
    {
        if (empty(trim($code))) {
            throw new \InvalidArgumentException('Referral code cannot be empty');
        }
    }

    public function value(): string
    {
        return $this->code;
    }

    public function equals(self $other): bool
    {
        return strtolower($this->code) === strtolower($other->code);
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
