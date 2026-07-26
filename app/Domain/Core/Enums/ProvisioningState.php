<?php

namespace App\Domain\Core\Enums;

enum ProvisioningState: string
{
    case Installing = 'installing';
    case Configuring = 'configuring';
    case Active = 'active';
    case Disabled = 'disabled';
    case Failed = 'failed';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Installing => $target === self::Configuring || $target === self::Failed,
            self::Configuring => $target === self::Active || $target === self::Failed,
            self::Active => $target === self::Disabled,
            self::Disabled => $target === self::Installing,
            self::Failed => $target === self::Installing,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Installing => 'Installing',
            self::Configuring => 'Configuring',
            self::Active => 'Active',
            self::Disabled => 'Disabled',
            self::Failed => 'Failed',
        };
    }
}
