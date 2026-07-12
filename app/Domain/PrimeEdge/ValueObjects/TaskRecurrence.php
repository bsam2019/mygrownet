<?php

namespace App\Domain\PrimeEdge\ValueObjects;

enum TaskRecurrence: string
{
    case ONE_OFF = 'one_off';
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case ANNUALLY = 'annually';

    public function label(): string
    {
        return match ($this) {
            self::ONE_OFF => 'One-off',
            self::MONTHLY => 'Monthly',
            self::QUARTERLY => 'Quarterly',
            self::ANNUALLY => 'Annually',
        };
    }

    public function isRecurring(): bool
    {
        return $this !== self::ONE_OFF;
    }
}
