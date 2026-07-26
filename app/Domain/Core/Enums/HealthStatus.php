<?php

namespace App\Domain\Core\Enums;

enum HealthStatus: string
{
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Maintenance = 'maintenance';
    case Unavailable = 'unavailable';
    case Offline = 'offline';

    public function isOperational(): bool
    {
        return match ($this) {
            self::Healthy, self::Degraded => true,
            self::Maintenance, self::Unavailable, self::Offline => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Healthy => 'Healthy',
            self::Degraded => 'Degraded',
            self::Maintenance => 'Maintenance',
            self::Unavailable => 'Unavailable',
            self::Offline => 'Offline',
        };
    }

    public function severity(): int
    {
        return match ($this) {
            self::Healthy => 0,
            self::Degraded => 1,
            self::Maintenance => 2,
            self::Unavailable => 3,
            self::Offline => 4,
        };
    }
}
