<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\ValueObjects;

enum VerificationLevel: string
{
    case Basic = 'basic';
    case Verified = 'verified';
    case Premium = 'premium';

    public function dailyWithdrawalLimit(): float
    {
        return match ($this) {
            self::Basic => 1000,
            self::Verified => 5000,
            self::Premium => 20000,
        };
    }
}
