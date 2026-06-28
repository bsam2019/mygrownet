<?php

namespace App\Domain\GrowBiz\ValueObjects;

enum BookingSource: string
{
    case ONLINE = 'online';
    case PHONE = 'phone';
    case WALK_IN = 'walk_in';
    case APP = 'app';
    case REFERRAL = 'referral';

    public function label(): string
    {
        return match($this) {
            self::ONLINE => 'Online Booking',
            self::PHONE => 'Phone',
            self::WALK_IN => 'Walk-in',
            self::APP => 'Mobile App',
            self::REFERRAL => 'Referral',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::ONLINE => 'globe',
            self::PHONE => 'phone',
            self::WALK_IN => 'user',
            self::APP => 'device-mobile',
            self::REFERRAL => 'users',
        };
    }
}
