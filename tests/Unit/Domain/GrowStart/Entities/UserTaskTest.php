<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStart\Entities;

use App\Domain\GrowStart\Entities\UserTask;
use App\Domain\GrowStart\ValueObjects\TaskStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class UserTaskTest extends TestCase
{
    public function test_can_create_user_task(): void
    {
        $userTask = UserTask::create(userJourneyId: 5, taskId: 10);

        $this->assertEquals(0, $userTask->getId());
        $this->assertEquals(5, $userTask->getUserJourneyId());
        $this->assertEquals(10, $userTask->getTaskId());
        $this->assertTrue($userTask->getStatus()->isPending());
        $this->assertNull($userTask->getStartedAt());
        $this->assertNull($userTask->getCompletedAt());
        $this->assertNull($userTask->getNotes());
        $this->assertEmpty($userTask->getAttachments());
    }

    public function test_can_reconstitute_user_task(): void
    {
        $startedAt = new DateTimeImmutable('2026-06-01 10:00:00');
        $completedAt = new DateTimeImmutable('2026-06-01 14:30:00');
        $createdAt = new DateTimeImmutable('2026-06-01 09:00:00');
        $updatedAt = new DateTimeImmutable('2026-06-01 14:30:00');

        $userTask = UserTask::reconstitute(
            id: 15,
            userJourneyId: 5,
            taskId: 10,
            status: TaskStatus::completed(),
            startedAt: $startedAt,
            completedAt: $completedAt,
            notes: 'All done',
            attachments: ['file1.pdf'],
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertEquals(15, $userTask->getId());
        $this->assertTrue($userTask->getStatus()->isCompleted());
        $this->assertEquals('All done', $userTask->getNotes());
        $this->assertEquals(['file1.pdf'], $userTask->getAttachments());
    }

    public function test_start_changes_status_to_in_progress(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->start();

        $this->assertTrue($userTask->getStatus()->isInProgress());
        $this->assertNotNull($userTask->getStartedAt());
    }

    public function test_start_does_nothing_if_already_in_progress(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->start();
        $startedAt = $userTask->getStartedAt();

        $userTask->start();
        $this->assertEquals($startedAt, $userTask->getStartedAt());
    }

    public function test_complete_sets_status_and_completed_at(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->complete();

        $this->assertTrue($userTask->getStatus()->isCompleted());
        $this->assertNotNull($userTask->getCompletedAt());
        $this->assertNotNull($userTask->getStartedAt());
        $this->assertEquals($userTask->getStartedAt(), $userTask->getCompletedAt());
    }

    public function test_complete_after_start_uses_real_start_time(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->start();
        $startedAt = $userTask->getStartedAt();

        $userTask->complete();
        $this->assertTrue($userTask->getStatus()->isCompleted());
        $this->assertEquals($startedAt, $userTask->getStartedAt());
    }

    public function test_skip_sets_status_to_skipped(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->skip();

        $this->assertTrue($userTask->getStatus()->isSkipped());
        $this->assertNotNull($userTask->getCompletedAt());
    }

    public function test_reset_returns_to_pending(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->start();
        $userTask->complete();
        $userTask->reset();

        $this->assertTrue($userTask->getStatus()->isPending());
        $this->assertNull($userTask->getStartedAt());
        $this->assertNull($userTask->getCompletedAt());
    }

    public function test_update_notes(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->updateNotes('Progress note');

        $this->assertEquals('Progress note', $userTask->getNotes());
    }

    public function test_add_attachment(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->addAttachment('path/to/file.pdf');

        $this->assertCount(1, $userTask->getAttachments());
        $this->assertContains('path/to/file.pdf', $userTask->getAttachments());
    }

    public function test_add_multiple_attachments(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->addAttachment('file1.pdf');
        $userTask->addAttachment('file2.pdf');

        $this->assertCount(2, $userTask->getAttachments());
    }

    public function test_remove_attachment(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->addAttachment('file1.pdf');
        $userTask->addAttachment('file2.pdf');
        $userTask->removeAttachment('file1.pdf');

        $this->assertCount(1, $userTask->getAttachments());
        $this->assertNotContains('file1.pdf', $userTask->getAttachments());
    }

    public function test_remove_attachment_that_does_not_exist(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->addAttachment('file1.pdf');
        $userTask->removeAttachment('nonexistent.pdf');

        $this->assertCount(1, $userTask->getAttachments());
    }

    public function test_get_duration_returns_null_when_not_started(): void
    {
        $userTask = UserTask::create(5, 10);
        $this->assertNull($userTask->getDurationInHours());
    }

    public function test_get_duration_returns_null_when_not_completed(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->start();
        $this->assertNull($userTask->getDurationInHours());
    }

    public function test_to_array_returns_expected_keys(): void
    {
        $userTask = UserTask::create(5, 10);
        $userTask->complete();
        $result = $userTask->toArray();

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('user_journey_id', $result);
        $this->assertArrayHasKey('task_id', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('started_at', $result);
        $this->assertArrayHasKey('completed_at', $result);
        $this->assertArrayHasKey('notes', $result);
        $this->assertArrayHasKey('attachments', $result);
        $this->assertArrayHasKey('duration_hours', $result);
        $this->assertEquals('completed', $result['status']);
    }
}
