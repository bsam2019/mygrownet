<?php

namespace App\Domain\Investor\ValueObjects;

enum ReportType: string
{
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case ANNUAL = 'annual';

    public function label(): string
    {
        return match($this) {
            self::MONTHLY => 'Monthly Report',
            self::QUARTERLY => 'Quarterly Report',
            self::ANNUAL => 'Annual Report',
        };
    }

    public function getDisplayPeriod(string $period): string
    {
        return match($this) {
            self::MONTHLY => date('F Y', strtotime($period . '-01')),
            self::QUARTERLY => $period,
            self::ANNUAL => 'Year ' . $period,
        };
    }

    public static function all(): array
    {
        return [
            self::MONTHLY,
            self::QUARTERLY,
            self::ANNUAL,
        ];
    }
}