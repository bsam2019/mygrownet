<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\ValueObjects;

enum CommissionLevel: int
{
    case Level1 = 1;
    case Level2 = 2;
    case Level3 = 3;
    case Level4 = 4;
    case Level5 = 5;
    case Level6 = 6;
    case Level7 = 7;

    public function rate(): float
    {
        return match ($this) {
            self::Level1 => config('mygrownet.commission_rates.1', 12.0),
            self::Level2 => config('mygrownet.commission_rates.2', 6.0),
            self::Level3 => config('mygrownet.commission_rates.3', 4.0),
            self::Level4 => config('mygrownet.commission_rates.4', 2.0),
            self::Level5 => config('mygrownet.commission_rates.5', 1.0),
            self::Level6 => 0.5,
            self::Level7 => 0.25,
        };
    }

    public function label(): string
    {
        return "Level {$this->value}";
    }
}
