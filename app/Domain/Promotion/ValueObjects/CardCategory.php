<?php

namespace App\Domain\Promotion\ValueObjects;

enum CardCategory: string
{
    case GENERAL = 'general';
    case OPPORTUNITY = 'opportunity';
    case TRAINING = 'training';
    case SUCCESS = 'success';
    case ANNOUNCEMENT = 'announcement';

    public function label(): string
    {
        return match($this) {
            self::GENERAL => 'General',
            self::OPPORTUNITY => 'Opportunity',
            self::TRAINING => 'Training',
            self::SUCCESS => 'Success Story',
            self::ANNOUNCEMENT => 'Announcement',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::GENERAL => 'blue',
            self::OPPORTUNITY => 'green',
            self::TRAINING => 'purple',
            self::SUCCESS => 'yellow',
            self::ANNOUNCEMENT => 'red',
        };
    }
}
