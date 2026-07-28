<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\ValueObjects;

enum AssetStatus: string
{
    case ACTIVE = 'active';
    case DISPOSED = 'disposed';
    case FULLY_DEPRECIATED = 'fully_depreciated';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::DISPOSED => 'Disposed',
            self::FULLY_DEPRECIATED => 'Fully Depreciated',
        };
    }
}
