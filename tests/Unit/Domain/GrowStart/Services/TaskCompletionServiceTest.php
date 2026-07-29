<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStart\Services;

use App\Domain\GrowStart\Entities\StartupJourney;
use App\Domain\GrowStart\Entities\UserTask;
use App\Domain\GrowStart\Exceptions\TaskNotFoundException;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;
use App\Domain\GrowStart\Repositories\TaskRepositoryInterface;
use App\Domain\GrowStart\Services\JourneyProgressService;
use App\Domain\GrowStart\Services\TaskCompletionService;
use PHPUnit\Framework\TestCase;

final class TaskCompletionServiceTest extends TestCase
{
    private TaskRepositoryInterface $taskRepository;
    private JourneyRepositoryInterface $journeyRepository;
    private StageRepositoryInterface $stageRepository;
    private JourneyProgressService $progressService;
    private TaskCompletionService $service;

    protected function setUp(): void
    {
        $this->taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $this->journeyRepository = $this->createMock(JourneyRepositoryInterface::class);
        $this->stageRepository = $this->createMock(StageRepositoryInterface::class);
        $this->progressService = $this->createMock(JourneyProgressService::class);
        $this->service = new TaskCompletionService(
            $this->taskRepository,
            $this->journeyRepository,
            $this->stageRepository,
            $this->progressService,
        );
    }

    public function test_complete_task_creates_user_task_if_not_exists(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);

        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTask')
            ->with(1, 5)
            ->willReturn(null);

        $this->taskRepository
            ->expects($this->once())
            ->method('saveUserTask')
            ->willReturnCallback(fn(UserTask $ut) => $ut);

        $this->journeyRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($journey);

        $this->progressService
            ->expects($this->once())
            ->method('canAdvanceToNextStage')
            ->willReturn(false);

        $result = $this->service->completeTask(1, 5);

        $this->assertTrue($result->getStatus()->isCompleted());
    }

    public function test_complete_task_updates_existing_user_task(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $existingTask = UserTask::create(1, 5);

        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTask')
            ->with(1, 5)
            ->willReturn($existingTask);

        $this->taskRepository
            ->expects($this->once())
            ->method('saveUserTask')
            ->willReturnCallback(fn(UserTask $ut) => $ut);

        $this->journeyRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($journey);

        $this->progressService
            ->expects($this->once())
            ->method('canAdvanceToNextStage')
            ->willReturn(false);

        $result = $this->service->completeTask(1, 5);

        $this->assertTrue($result->getStatus()->isCompleted());
        $this->assertEquals(1, $result->getUserJourneyId());
        $this->assertEquals(5, $result->getTaskId());
    }

    public function test_complete_task_checks_stage_advancement(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);

        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTask')
            ->willReturn(null);

        $this->taskRepository
            ->expects($this->once())
            ->method('saveUserTask')
            ->willReturnCallback(fn(UserTask $ut) => $ut);

        $this->progressService
            ->expects($this->once())
            ->method('canAdvanceToNextStage')
            ->willReturn(true);

        $this->journeyRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($journey);

        $this->stageRepository
            ->expects($this->once())
            ->method('findNext')
            ->with(1)
            ->willReturn(null);

        $this->journeyRepository
            ->expects($this->once())
            ->method('save')
            ->with($journey);

        $result = $this->service->completeTask(1, 5);

        $this->assertTrue($result->getStatus()->isCompleted());
    }

    public function test_start_task_creates_if_not_exists(): void
    {
        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTask')
            ->with(1, 5)
            ->willReturn(null);

        $this->taskRepository
            ->expects($this->once())
            ->method('saveUserTask')
            ->willReturnCallback(fn(UserTask $ut) => $ut);

        $result = $this->service->startTask(1, 5);

        $this->assertTrue($result->getStatus()->isInProgress());
    }

    public function test_start_task_starts_existing(): void
    {
        $existingTask = UserTask::create(1, 5);

        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTask')
            ->willReturn($existingTask);

        $this->taskRepository
            ->expects($this->once())
            ->method('saveUserTask')
            ->willReturnCallback(fn(UserTask $ut) => $ut);

        $result = $this->service->startTask(1, 5);

        $this->assertTrue($result->getStatus()->isInProgress());
    }

    public function test_skip_task_creates_if_not_exists(): void
    {
        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);

        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTask')
            ->willReturn(null);

        $this->taskRepository
            ->expects($this->once())
            ->method('saveUserTask')
            ->willReturnCallback(fn(UserTask $ut) => $ut);

        $this->journeyRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($journey);

        $this->progressService
            ->expects($this->once())
            ->method('canAdvanceToNextStage')
            ->willReturn(false);

        $result = $this->service->skipTask(1, 5);

        $this->assertTrue($result->getStatus()->isSkipped());
    }

    public function test_reset_task_throws_when_not_found(): void
    {
        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTask')
            ->willReturn(null);

        $this->expectException(TaskNotFoundException::class);
        $this->service->resetTask(1, 5);
    }

    public function test_reset_task_resets_existing(): void
    {
        $completedTask = UserTask::create(1, 5);
        $completedTask->complete();

        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTask')
            ->willReturn($completedTask);

        $this->taskRepository
            ->expects($this->once())
            ->method('saveUserTask')
            ->willReturnCallback(fn(UserTask $ut) => $ut);

        $result = $this->service->resetTask(1, 5);

        $this->assertTrue($result->getStatus()->isPending());
        $this->assertNull($result->getStartedAt());
    }

    public function test_update_task_notes(): void
    {
        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTask')
            ->willReturn(null);

        $this->taskRepository
            ->expects($this->once())
            ->method('saveUserTask')
            ->willReturnCallback(fn(UserTask $ut) => $ut);

        $result = $this->service->updateTaskNotes(1, 5, 'New notes');

        $this->assertEquals('New notes', $result->getNotes());
    }

    public function test_add_task_attachment(): void
    {
        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTask')
            ->willReturn(null);

        $this->taskRepository
            ->expects($this->once())
            ->method('saveUserTask')
            ->willReturnCallback(fn(UserTask $ut) => $ut);

        $result = $this->service->addTaskAttachment(1, 5, '/path/file.pdf');

        $this->assertContains('/path/file.pdf', $result->getAttachments());
    }
}
