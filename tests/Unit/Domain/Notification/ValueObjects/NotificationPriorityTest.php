<?php

namespace Tests\Unit\Domain\Notification\ValueObjects;

use App\Domain\Notification\ValueObjects\NotificationPriority;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NotificationPriorityTest extends TestCase
{
    public function test_low_case(): void
    {
        $this->assertEquals('low', NotificationPriority::LOW->value);
        $this->assertFalse(NotificationPriority::LOW->isUrgent());
        $this->assertFalse(NotificationPriority::LOW->isHighOrAbove());
    }

    public function test_normal_case(): void
    {
        $this->assertEquals('normal', NotificationPriority::NORMAL->value);
        $this->assertFalse(NotificationPriority::NORMAL->isUrgent());
        $this->assertFalse(NotificationPriority::NORMAL->isHighOrAbove());
    }

    public function test_high_case(): void
    {
        $this->assertEquals('high', NotificationPriority::HIGH->value);
        $this->assertFalse(NotificationPriority::HIGH->isUrgent());
        $this->assertTrue(NotificationPriority::HIGH->isHighOrAbove());
    }

    public function test_urgent_case(): void
    {
        $this->assertEquals('urgent', NotificationPriority::URGENT->value);
        $this->assertTrue(NotificationPriority::URGENT->isUrgent());
        $this->assertTrue(NotificationPriority::URGENT->isHighOrAbove());
    }

    public function test_from_valid_string(): void
    {
        $this->assertEquals(NotificationPriority::LOW, NotificationPriority::from('low'));
        $this->assertEquals(NotificationPriority::NORMAL, NotificationPriority::from('normal'));
        $this->assertEquals(NotificationPriority::HIGH, NotificationPriority::from('high'));
        $this->assertEquals(NotificationPriority::URGENT, NotificationPriority::from('urgent'));
    }

    public function test_try_from_valid_string(): void
    {
        $this->assertEquals(NotificationPriority::NORMAL, NotificationPriority::tryFrom('normal'));
        $this->assertNull(NotificationPriority::tryFrom('invalid'));
    }
}
