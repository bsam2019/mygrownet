<?php

namespace App\Domain\Investor\ValueObjects;

enum AnnouncementType: string
{
    case GENERAL = 'general';
    case FINANCIAL = 'financial';
    case DIVIDEND = 'dividend';
    case MEETING = 'meeting';
    case URGENT = 'urgent';
    case MILESTONE = 'milestone';

    public function label(): string
    {
        return match($this) {
            self::GENERAL => 'General Update',
            self::FINANCIAL => 'Financial News',
            self::DIVIDEND => 'Dividend Announcement',
            self::MEETING => 'Meeting Notice',
            self::URGENT => 'Urgent Notice',
            self::MILESTONE => 'Company Milestone',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::GENERAL => 'megaphone',
            self::FINANCIAL => 'chart-bar',
            self::DIVIDEND => 'banknotes',
            self::MEETING => 'calendar',
            self::URGENT => 'exclamation-triangle',
            self::MILESTONE => 'trophy',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::GENERAL => 'blue',
            self::FINANCIAL => 'green',
            self::DIVIDEND => 'emerald',
            self::MEETING => 'purple',
            self::URGENT => 'red',
            self::MILESTONE => 'amber',
        };
    }
}
