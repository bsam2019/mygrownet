<?php

namespace App\Domain\Ubumi\ValueObjects;

enum CheckInStatus: string
{
    case WELL = 'well';
    case UNWELL = 'unwell';
    case NEED_ASSISTANCE = 'need_assistance';

    public function label(): string
    {
        return match($this) {
            self::WELL => 'I am well',
            self::UNWELL => 'I am not feeling well',
            self::NEED_ASSISTANCE => 'I need assistance',
        };
    }

    public function emoji(): string
    {
        return match($this) {
            self::WELL => '😊',
            self::UNWELL => '😐',
            self::NEED_ASSISTANCE => '🆘',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::WELL => 'green',
            self::UNWELL => 'amber',
            self::NEED_ASSISTANCE => 'red',
        };
    }

    public function requiresAlert(): bool
    {
        return $this === self::UNWELL || $this === self::NEED_ASSISTANCE;
    }
}
