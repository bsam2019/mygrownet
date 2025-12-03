<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\ValueObjects;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case BANK = 'bank';
    case MOBILE_MONEY = 'mobile_money';
    case CHEQUE = 'cheque';
    case CREDIT = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::BANK => 'Bank Transfer',
            self::MOBILE_MONEY => 'Mobile Money',
            self::CHEQUE => 'Cheque',
            self::CREDIT => 'On Credit',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::CASH => 'banknotes',
            self::BANK => 'building-library',
            self::MOBILE_MONEY => 'device-phone-mobile',
            self::CHEQUE => 'document-text',
            self::CREDIT => 'credit-card',
        };
    }
}
