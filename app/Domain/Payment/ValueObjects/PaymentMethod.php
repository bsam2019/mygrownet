<?php

namespace App\Domain\Payment\ValueObjects;

use InvalidArgumentException;

enum PaymentMethod: string
{
    case MTN_MOMO = 'mtn_momo';
    case AIRTEL_MONEY = 'airtel_money';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH = 'cash';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::MTN_MOMO => 'MTN MoMo',
            self::AIRTEL_MONEY => 'Airtel Money',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::CASH => 'Cash',
            self::OTHER => 'Other',
        };
    }

    public static function fromString(string $value): self
    {
        return self::from($value);
    }
}
