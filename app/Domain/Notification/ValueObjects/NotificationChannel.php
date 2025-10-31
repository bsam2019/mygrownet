<?php

namespace App\Domain\Notification\ValueObjects;

enum NotificationChannel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case PUSH = 'push';
    case IN_APP = 'in_app';

    public function requiresExternalProvider(): bool
    {
        return in_array($this, [self::SMS, self::PUSH]);
    }

    public function isFree(): bool
    {
        return in_array($this, [self::EMAIL, self::IN_APP]);
    }
}
