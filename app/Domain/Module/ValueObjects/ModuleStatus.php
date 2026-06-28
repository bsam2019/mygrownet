<?php

namespace App\Domain\Module\ValueObjects;

enum ModuleStatus: string
{
    case ACTIVE = 'active';
    case BETA = 'beta';
    case COMING_SOON = 'coming_soon';
    case INACTIVE = 'inactive';

    public static function fromString(string $status): self
    {
        return self::tryFrom($status) ?? self::ACTIVE;
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isBeta(): bool
    {
        return $this === self::BETA;
    }

    public function isComingSoon(): bool
    {
        return $this === self::COMING_SOON;
    }

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::BETA => 'Beta',
            self::COMING_SOON => 'Coming Soon',
            self::INACTIVE => 'Inactive',
        };
    }
}
