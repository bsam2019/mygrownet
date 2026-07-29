<?php

namespace Tests\Unit\Domain\LifePlus\Entities;

use App\Domain\LifePlus\Entities\LifePlusHabit;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LifePlusHabitTest extends TestCase
{
    #[Test]
    public function reconstitute_sets_all_fields()
    {
        $createdAt = new DateTimeImmutable('2026-08-01 06:00:00');
        $updatedAt = new DateTimeImmutable('2026-08-15 07:30:00');

        $habit = LifePlusHabit::reconstitute([
            'id' => 1,
            'user_id' => 42,
            'name' => 'Morning run',
            'icon' => '🏃',
            'color' => '#3b82f6',
            'frequency' => 'daily',
            'reminder_time' => '06:00',
            'is_active' => true,
            'created_at' => '2026-08-01 06:00:00',
            'updated_at' => '2026-08-15 07:30:00',
        ]);

        $this->assertSame(1, $habit->id);
        $this->assertSame(42, $habit->userId);
        $this->assertSame('Morning run', $habit->name);
        $this->assertSame('🏃', $habit->icon);
        $this->assertSame('#3b82f6', $habit->color);
        $this->assertSame('daily', $habit->frequency);
        $this->assertSame('06:00', $habit->reminderTime);
        $this->assertTrue($habit->isActive);
        $this->assertEquals($createdAt, $habit->createdAt);
        $this->assertEquals($updatedAt, $habit->updatedAt);
    }

    #[Test]
    public function reconstitute_applies_defaults()
    {
        $habit = LifePlusHabit::reconstitute([
            'user_id' => 1,
            'name' => 'Drink water',
        ]);

        $this->assertNull($habit->id);
        $this->assertSame('⭐', $habit->icon);
        $this->assertSame('#10b981', $habit->color);
        $this->assertSame('daily', $habit->frequency);
        $this->assertNull($habit->reminderTime);
        $this->assertTrue($habit->isActive);
        $this->assertNull($habit->createdAt);
        $this->assertNull($habit->updatedAt);
    }

    #[Test]
    public function toArray_round_trips_all_fields()
    {
        $data = [
            'id' => 5,
            'user_id' => 99,
            'name' => 'Read',
            'icon' => '📖',
            'color' => '#8b5cf6',
            'frequency' => 'weekly',
            'reminder_time' => '20:00',
            'is_active' => false,
            'created_at' => '2026-08-10 10:00:00',
            'updated_at' => null,
        ];

        $habit = LifePlusHabit::reconstitute($data);
        $result = $habit->toArray();

        $this->assertSame($data['id'], $result['id']);
        $this->assertSame($data['user_id'], $result['user_id']);
        $this->assertSame($data['name'], $result['name']);
        $this->assertSame($data['icon'], $result['icon']);
        $this->assertSame($data['color'], $result['color']);
        $this->assertSame($data['frequency'], $result['frequency']);
        $this->assertSame($data['reminder_time'], $result['reminder_time']);
        $this->assertSame($data['is_active'], $result['is_active']);
        $this->assertSame($data['created_at'], $result['created_at']);
        $this->assertNull($result['updated_at']);
    }
}
