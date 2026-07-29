<?php

namespace Tests\Unit\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusTask;
use App\Domain\LifePlus\Repositories\TaskRepositoryInterface;
use App\Domain\LifePlus\Services\TaskService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TaskServiceTest extends TestCase
{
    private TaskRepositoryInterface $taskRepo;
    private TaskService $service;

    protected function setUp(): void
    {
        $this->taskRepo = $this->createMock(TaskRepositoryInterface::class);
        $this->service = new TaskService($this->taskRepo);
    }

    #[Test]
    public function getTasks_returns_mapped_tasks()
    {
        $task = LifePlusTask::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Test', 'priority' => 'high', 'due_date' => '2026-09-01']);

        $this->taskRepo->expects($this->once())->method('findByUser')->with(42, [])->willReturn([$task]);

        $result = $this->service->getTasks(42);

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('Test', $result[0]['title']);
        $this->assertSame('#dc2626', $result[0]['priority_color']);
    }

    #[Test]
    public function getTodayTasks_passes_today_filter()
    {
        $this->taskRepo->expects($this->once())->method('findByUser')->with(42, ['today' => true, 'is_completed' => false])->willReturn([]);

        $this->assertSame([], $this->service->getTodayTasks(42));
    }

    #[Test]
    public function createTask_saves_and_returns_mapped()
    {
        $saved = LifePlusTask::reconstitute(['id' => 10, 'user_id' => 42, 'title' => 'New task', 'priority' => 'low', 'is_synced' => true]);

        $this->taskRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->createTask(42, ['title' => 'New task', 'priority' => 'low', 'is_synced' => false]);

        $this->assertSame(10, $result['id']);
        $this->assertSame('New task', $result['title']);
        $this->assertSame('#10b981', $result['priority_color']);
    }

    #[Test]
    public function updateTask_returns_null_when_not_found()
    {
        $this->taskRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertNull($this->service->updateTask(1, 42, ['title' => 'Updated']));
    }

    #[Test]
    public function updateTask_returns_null_on_user_mismatch()
    {
        $task = LifePlusTask::reconstitute(['id' => 1, 'user_id' => 99, 'title' => 'Other']);
        $this->taskRepo->expects($this->once())->method('findById')->with(1)->willReturn($task);

        $this->assertNull($this->service->updateTask(1, 42, ['title' => 'Updated']));
    }

    #[Test]
    public function updateTask_merges_and_saves()
    {
        $task = LifePlusTask::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Old', 'priority' => 'low']);
        $updated = LifePlusTask::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Updated', 'priority' => 'high']);

        $this->taskRepo->expects($this->once())->method('findById')->with(1)->willReturn($task);
        $this->taskRepo->expects($this->once())->method('save')->willReturn($updated);

        $result = $this->service->updateTask(1, 42, ['title' => 'Updated', 'priority' => 'high']);

        $this->assertSame('Updated', $result['title']);
    }

    #[Test]
    public function toggleTask_flips_completed_state()
    {
        $task = LifePlusTask::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Task', 'is_completed' => false]);
        $toggled = LifePlusTask::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Task', 'is_completed' => true, 'completed_at' => '2026-08-16 10:00:00']);

        $this->taskRepo->expects($this->once())->method('findById')->with(1)->willReturn($task);
        $this->taskRepo->expects($this->once())->method('save')->willReturn($toggled);

        $result = $this->service->toggleTask(1, 42);

        $this->assertTrue($result['is_completed']);
    }

    #[Test]
    public function toggleTask_returns_null_on_not_found()
    {
        $this->taskRepo->expects($this->once())->method('findById')->with(99)->willReturn(null);

        $this->assertNull($this->service->toggleTask(99, 42));
    }

    #[Test]
    public function deleteTask_returns_true_on_success()
    {
        $task = LifePlusTask::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Delete me']);
        $this->taskRepo->expects($this->once())->method('findById')->with(1)->willReturn($task);
        $this->taskRepo->expects($this->once())->method('delete')->with(1)->willReturn(true);

        $this->assertTrue($this->service->deleteTask(1, 42));
    }

    #[Test]
    public function deleteTask_returns_false_on_not_found()
    {
        $this->taskRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertFalse($this->service->deleteTask(1, 42));
    }

    #[Test]
    public function deleteTask_returns_false_on_user_mismatch()
    {
        $task = LifePlusTask::reconstitute(['id' => 1, 'user_id' => 99, 'title' => 'Other']);
        $this->taskRepo->expects($this->once())->method('findById')->with(1)->willReturn($task);

        $this->assertFalse($this->service->deleteTask(1, 42));
    }

    #[Test]
    public function syncTasks_creates_new_when_no_local_id()
    {
        $saved = LifePlusTask::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Synced', 'is_synced' => true]);
        $this->taskRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->syncTasks(42, [['title' => 'Synced']]);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function syncTasks_updates_existing_when_local_id_matches()
    {
        $existing = LifePlusTask::reconstitute(['id' => 5, 'user_id' => 42, 'title' => 'Old', 'local_id' => 'local-1']);
        $updated = LifePlusTask::reconstitute(['id' => 5, 'user_id' => 42, 'title' => 'Updated', 'local_id' => 'local-1']);

        $this->taskRepo->expects($this->once())->method('findByLocalId')->with(42, 'local-1')->willReturn($existing);
        $this->taskRepo->expects($this->once())->method('findById')->with(5)->willReturn($existing);
        $this->taskRepo->expects($this->once())->method('save')->willReturn($updated);

        $result = $this->service->syncTasks(42, [['title' => 'Updated', 'local_id' => 'local-1']]);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function syncTasks_creates_new_when_local_id_not_found()
    {
        $this->taskRepo->expects($this->once())->method('findByLocalId')->with(42, 'local-new')->willReturn(null);
        $saved = LifePlusTask::reconstitute(['id' => 10, 'user_id' => 42, 'title' => 'New', 'local_id' => 'local-new']);
        $this->taskRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->syncTasks(42, [['title' => 'New', 'local_id' => 'local-new']]);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function getStats_delegates_to_repo()
    {
        $stats = ['completed' => 5, 'pending' => 3, 'completion_rate' => 62.5];
        $this->taskRepo->expects($this->once())->method('getStats')->with(42)->willReturn($stats);

        $this->assertSame($stats, $this->service->getStats(42));
    }

    #[Test]
    public function getTasksForMonth_delegates_to_repo()
    {
        $task = LifePlusTask::reconstitute(['id' => 1, 'user_id' => 42, 'title' => 'Monthly', 'due_date' => '2026-08-15']);
        $this->taskRepo->expects($this->once())->method('getForMonth')->with(42, '2026-08')->willReturn([$task]);

        $result = $this->service->getTasksForMonth(42, '2026-08');

        $this->assertCount(1, $result);
        $this->assertSame('Monthly', $result[0]['title']);
    }
}
