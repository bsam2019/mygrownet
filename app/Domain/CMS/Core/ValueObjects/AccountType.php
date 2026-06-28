<?php

declare(strict_types=1);

namespace App\Domain\CMS\Core\ValueObjects;

enum AccountType: string
{
    case ASSET = 'asset';
    case LIABILITY = 'liability';
    case EQUITY = 'equity';
    case INCOME = 'income';
    case EXPENSE = 'expense';

    public function label(): string
    {
        return match($this) {
            self::ASSET => 'Assets',
            self::LIABILITY => 'Liabilities',
            self::EQUITY => 'Equity',
            self::INCOME => 'Income',
            self::EXPENSE => 'Expenses',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ASSET => 'blue',
            self::LIABILITY => 'red',
            self::EQUITY => 'purple',
            self::INCOME => 'emerald',
            self::EXPENSE => 'amber',
        };
    }

    public function isDebitNormal(): bool
    {
        return match($this) {
            self::ASSET, self::EXPENSE => true,
            self::LIABILITY, self::EQUITY, self::INCOME => false,
        };
    }
}
