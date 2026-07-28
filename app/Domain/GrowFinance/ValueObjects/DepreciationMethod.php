<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\ValueObjects;

enum DepreciationMethod: string
{
    case STRAIGHT_LINE = 'straight_line';
    case REDUCING_BALANCE = 'reducing_balance';

    public function label(): string
    {
        return match ($this) {
            self::STRAIGHT_LINE => 'Straight Line',
            self::REDUCING_BALANCE => 'Reducing Balance',
        };
    }
}
