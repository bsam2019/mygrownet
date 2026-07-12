<?php

namespace App\Domain\PrimeEdge\ValueObjects;

enum ComplianceType: string
{
    case PAYE = 'paye';
    case NAPSA = 'napsa';
    case NHIMA = 'nhima';
    case VAT = 'vat';
    case TAX_RETURN = 'tax_return';
    case ANNUAL_RETURN = 'annual_return';
    case BUSINESS_RENEWAL = 'business_renewal';
    case NAPSA_RETURN = 'napsa_return';
    case NHIMA_RETURN = 'nhima_return';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::PAYE => 'PAYE',
            self::NAPSA => 'NAPSA',
            self::NHIMA => 'NHIMA',
            self::VAT => 'VAT',
            self::TAX_RETURN => 'Income Tax Return',
            self::ANNUAL_RETURN => 'Annual Return (PACRA)',
            self::BUSINESS_RENEWAL => 'Business Registration Renewal',
            self::NAPSA_RETURN => 'NAPSA Return',
            self::NHIMA_RETURN => 'NHIMA Return',
            self::OTHER => 'Other Compliance',
        };
    }

    public function defaultReminderDays(): int
    {
        return match ($this) {
            self::PAYE, self::NAPSA, self::NHIMA => 7,
            self::VAT => 14,
            self::TAX_RETURN, self::ANNUAL_RETURN => 30,
            self::BUSINESS_RENEWAL => 60,
            self::NAPSA_RETURN, self::NHIMA_RETURN => 7,
            self::OTHER => 14,
        };
    }
}
