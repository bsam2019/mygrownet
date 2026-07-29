<?php

namespace Tests\Unit\Domain\Notification\ValueObjects;

use App\Domain\Notification\ValueObjects\NotificationChannel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NotificationChannelTest extends TestCase
{
    public function test_email_case(): void
    {
        $this->assertEquals('email', NotificationChannel::EMAIL->value);
        $this->assertFalse(NotificationChannel::EMAIL->requiresExternalProvider());
        $this->assertTrue(NotificationChannel::EMAIL->isFree());
    }

    public function test_sms_case(): void
    {
        $this->assertEquals('sms', NotificationChannel::SMS->value);
        $this->assertTrue(NotificationChannel::SMS->requiresExternalProvider());
        $this->assertFalse(NotificationChannel::SMS->isFree());
    }

    public function test_push_case(): void
    {
        $this->assertEquals('push', NotificationChannel::PUSH->value);
        $this->assertTrue(NotificationChannel::PUSH->requiresExternalProvider());
        $this->assertFalse(NotificationChannel::PUSH->isFree());
    }

    public function test_in_app_case(): void
    {
        $this->assertEquals('in_app', NotificationChannel::IN_APP->value);
        $this->assertFalse(NotificationChannel::IN_APP->requiresExternalProvider());
        $this->assertTrue(NotificationChannel::IN_APP->isFree());
    }

    public function test_from_valid_string(): void
    {
        $this->assertEquals(NotificationChannel::EMAIL, NotificationChannel::from('email'));
        $this->assertEquals(NotificationChannel::SMS, NotificationChannel::from('sms'));
        $this->assertEquals(NotificationChannel::PUSH, NotificationChannel::from('push'));
        $this->assertEquals(NotificationChannel::IN_APP, NotificationChannel::from('in_app'));
    }

    public function test_try_from_valid_string(): void
    {
        $this->assertEquals(NotificationChannel::EMAIL, NotificationChannel::tryFrom('email'));
        $this->assertNull(NotificationChannel::tryFrom('invalid'));
    }
}
