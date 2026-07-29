<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStart\ValueObjects;

use App\Domain\GrowStart\ValueObjects\TaskStatus;
use PHPUnit\Framework\TestCase;

final class TaskStatusTest extends TestCase
{
    public function test_pending_is_pending(): void
    {
        $status = TaskStatus::pending();
        $this->assertTrue($status->isPending());
        $this->assertFalse($status->isInProgress());
        $this->assertFalse($status->isCompleted());
        $this->assertFalse($status->isSkipped());
    }

    public function test_in_progress_is_in_progress(): void
    {
        $status = TaskStatus::inProgress();
        $this->assertTrue($status->isInProgress());
        $this->assertFalse($status->isPending());
    }

    public function test_completed_is_completed(): void
    {
        $status = TaskStatus::completed();
        $this->assertTrue($status->isCompleted());
        $this->assertFalse($status->isPending());
    }

    public function test_skipped_is_skipped(): void
    {
        $status = TaskStatus::skipped();
        $this->assertTrue($status->isSkipped());
        $this->assertFalse($status->isPending());
    }

    public function test_is_done_returns_true_for_completed(): void
    {
        $this->assertTrue(TaskStatus::completed()->isDone());
    }

    public function test_is_done_returns_true_for_skipped(): void
    {
        $this->assertTrue(TaskStatus::skipped()->isDone());
    }

    public function test_is_done_returns_false_for_pending(): void
    {
        $this->assertFalse(TaskStatus::pending()->isDone());
    }

    public function test_is_done_returns_false_for_in_progress(): void
    {
        $this->assertFalse(TaskStatus::inProgress()->isDone());
    }

    public function test_can_create_from_string(): void
    {
        $this->assertTrue(TaskStatus::fromString('completed')->isCompleted());
        $this->assertTrue(TaskStatus::fromString('pending')->isPending());
    }

    public function test_cannot_create_from_invalid_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TaskStatus::fromString('invalid');
    }

    public function test_value_returns_string(): void
    {
        $this->assertEquals('pending', TaskStatus::pending()->value());
        $this->assertEquals('in_progress', TaskStatus::inProgress()->value());
        $this->assertEquals('completed', TaskStatus::completed()->value());
        $this->assertEquals('skipped', TaskStatus::skipped()->value());
    }

    public function test_equals_returns_true_for_same_status(): void
    {
        $this->assertTrue(TaskStatus::completed()->equals(TaskStatus::completed()));
        $this->assertFalse(TaskStatus::completed()->equals(TaskStatus::pending()));
    }

    public function test_to_string_returns_value(): void
    {
        $this->assertEquals('completed', (string) TaskStatus::completed());
    }
}
