<?php

namespace Tests\Unit\Domain\LifePlus\Entities;

use App\Domain\LifePlus\Entities\LifePlusTask;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LifePlusTaskTest extends TestCase
{
    #[Test]
    public function reconstitute_sets_all_fields()
    {
        $dueDate = new DateTimeImmutable('2026-08-15');
        $completedAt = new DateTimeImmutable('2026-08-16 10:00:00');
        $createdAt = new DateTimeImmutable('2026-08-10 08:00:00');
        $updatedAt = new DateTimeImmutable('2026-08-16 10:05:00');

        $task = LifePlusTask::reconstitute([
            'id' => 1,
            'user_id' => 42,
            'title' => 'Buy groceries',
            'description' => 'Milk, eggs, bread',
            'priority' => 'high',
            'due_date' => '2026-08-15',
            'due_time' => '14:00',
            'is_completed' => true,
            'completed_at' => '2026-08-16 10:00:00',
            'is_synced' => false,
            'local_id' => 'local-abc',
            'created_at' => '2026-08-10 08:00:00',
            'updated_at' => '2026-08-16 10:05:00',
        ]);

        $this->assertSame(1, $task->id);
        $this->assertSame(42, $task->userId);
        $this->assertSame('Buy groceries', $task->title);
        $this->assertSame('Milk, eggs, bread', $task->description);
        $this->assertSame('high', $task->priority);
        $this->assertEquals($dueDate, $task->dueDate);
        $this->assertSame('14:00', $task->dueTime);
        $this->assertTrue($task->isCompleted);
        $this->assertEquals($completedAt, $task->completedAt);
        $this->assertFalse($task->isSynced);
        $this->assertSame('local-abc', $task->localId);
        $this->assertEquals($createdAt, $task->createdAt);
        $this->assertEquals($updatedAt, $task->updatedAt);
    }

    #[Test]
    public function reconstitute_applies_defaults()
    {
        $task = LifePlusTask::reconstitute([
            'user_id' => 1,
            'title' => 'Test',
        ]);

        $this->assertNull($task->id);
        $this->assertSame('medium', $task->priority);
        $this->assertNull($task->dueDate);
        $this->assertNull($task->dueTime);
        $this->assertFalse($task->isCompleted);
        $this->assertNull($task->completedAt);
        $this->assertTrue($task->isSynced);
        $this->assertNull($task->localId);
        $this->assertNull($task->createdAt);
        $this->assertNull($task->updatedAt);
    }

    #[Test]
    public function toArray_round_trips_all_fields()
    {
        $data = [
            'id' => 5,
            'user_id' => 99,
            'title' => 'Write report',
            'description' => 'Q3 summary',
            'priority' => 'high',
            'due_date' => '2026-09-01',
            'due_time' => '17:00',
            'is_completed' => false,
            'completed_at' => null,
            'is_synced' => true,
            'local_id' => 'local-xyz',
            'created_at' => '2026-08-20 09:00:00',
            'updated_at' => '2026-08-20 09:30:00',
        ];

        $task = LifePlusTask::reconstitute($data);
        $result = $task->toArray();

        $this->assertSame($data['id'], $result['id']);
        $this->assertSame($data['user_id'], $result['user_id']);
        $this->assertSame($data['title'], $result['title']);
        $this->assertSame($data['description'], $result['description']);
        $this->assertSame($data['priority'], $result['priority']);
        $this->assertSame($data['due_date'], $result['due_date']);
        $this->assertSame($data['due_time'], $result['due_time']);
        $this->assertSame($data['is_completed'], $result['is_completed']);
        $this->assertNull($result['completed_at']);
        $this->assertSame($data['is_synced'], $result['is_synced']);
        $this->assertSame($data['local_id'], $result['local_id']);
        $this->assertSame($data['created_at'], $result['created_at']);
        $this->assertSame($data['updated_at'], $result['updated_at']);
    }

    #[Test]
    public function toArray_with_completed_at_formats_correctly()
    {
        $task = LifePlusTask::reconstitute([
            'user_id' => 1,
            'title' => 'Done',
            'is_completed' => true,
            'completed_at' => '2026-08-16 10:00:00',
        ]);

        $result = $task->toArray();
        $this->assertSame('2026-08-16 10:00:00', $result['completed_at']);
    }

    #[Test]
    public function toArray_null_dates_return_null()
    {
        $task = LifePlusTask::reconstitute([
            'user_id' => 1,
            'title' => 'No dates',
        ]);

        $result = $task->toArray();
        $this->assertNull($result['due_date']);
        $this->assertNull($result['completed_at']);
        $this->assertNull($result['created_at']);
        $this->assertNull($result['updated_at']);
    }
}
