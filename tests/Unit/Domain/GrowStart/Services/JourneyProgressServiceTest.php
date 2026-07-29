<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStart\Services;

use App\Domain\GrowStart\Entities\Stage;
use App\Domain\GrowStart\Entities\StartupJourney;
use App\Domain\GrowStart\Entities\UserTask;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;
use App\Domain\GrowStart\Repositories\TaskRepositoryInterface;
use App\Domain\GrowStart\Services\JourneyProgressService;
use App\Domain\GrowStart\ValueObjects\StageSlug;
use App\Domain\GrowStart\ValueObjects\TaskStatus;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class JourneyProgressServiceTest extends TestCase
{
    private TaskRepositoryInterface $taskRepository;
    private StageRepositoryInterface $stageRepository;
    private JourneyProgressService $service;

    protected function setUp(): void
    {
        $this->taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $this->stageRepository = $this->createMock(StageRepositoryInterface::class);
        $this->service = new JourneyProgressService($this->taskRepository, $this->stageRepository);
    }

    public function test_calculate_progress_with_no_tasks(): void
    {
        $this->stageRepository
            ->expects($this->once())
            ->method('findActive')
            ->willReturn(new Collection());

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $progress = $this->service->calculateProgress($journey);

        $this->assertEquals(0, $progress->overall());
        $this->assertEquals(0, $progress->tasksCompleted());
        $this->assertEquals(0, $progress->totalTasks());
    }

    public function test_calculate_progress_with_tasks(): void
    {
        $stage = Stage::create('Idea', StageSlug::idea(), 1);

        $this->stageRepository
            ->expects($this->once())
            ->method('findActive')
            ->willReturn(new Collection([$stage]));

        $this->taskRepository
            ->expects($this->once())
            ->method('countCompletedByStage')
            ->with($this->anything(), $stage->getId())
            ->willReturn(3);

        $this->taskRepository
            ->expects($this->once())
            ->method('countTotalByStage')
            ->with($this->anything(), $stage->getId())
            ->willReturn(6);

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $progress = $this->service->calculateProgress($journey);

        $this->assertEquals(50, $progress->overall());
        $this->assertEquals(3, $progress->tasksCompleted());
        $this->assertEquals(6, $progress->totalTasks());
    }

    public function test_calculate_progress_with_multiple_stages(): void
    {
        $stage1 = Stage::reconstitute(1, 'Idea', StageSlug::idea(), 'Validate', 1, null, null, 7, true);
        $stage2 = Stage::reconstitute(2, 'Validation', StageSlug::validation(), 'Research', 2, null, null, 7, true);

        $this->stageRepository
            ->expects($this->once())
            ->method('findActive')
            ->willReturn(new Collection([$stage1, $stage2]));

        $this->taskRepository
            ->expects($this->exactly(2))
            ->method('countCompletedByStage')
            ->willReturnCallback(function ($journeyId, $stageId) use ($stage1, $stage2) {
                return match ($stageId) {
                    $stage1->getId() => 4,
                    $stage2->getId() => 1,
                    default => 0,
                };
            });

        $this->taskRepository
            ->expects($this->exactly(2))
            ->method('countTotalByStage')
            ->willReturnCallback(function ($journeyId, $stageId) use ($stage1, $stage2) {
                return match ($stageId) {
                    $stage1->getId() => 5,
                    $stage2->getId() => 5,
                    default => 0,
                };
            });

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $progress = $this->service->calculateProgress($journey);

        $this->assertEquals(50, $progress->overall());
        $this->assertEquals(5, $progress->tasksCompleted());
        $this->assertEquals(10, $progress->totalTasks());
    }

    public function test_calculate_stage_progress_returns_zero_when_no_tasks(): void
    {
        $this->taskRepository
            ->expects($this->once())
            ->method('countCompletedByStage')
            ->willReturn(0);

        $this->taskRepository
            ->expects($this->once())
            ->method('countTotalByStage')
            ->willReturn(0);

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $result = $this->service->calculateStageProgress($journey, 1);

        $this->assertEquals(0, $result);
    }

    public function test_calculate_stage_progress_returns_percentage(): void
    {
        $this->taskRepository
            ->expects($this->once())
            ->method('countCompletedByStage')
            ->willReturn(3);

        $this->taskRepository
            ->expects($this->once())
            ->method('countTotalByStage')
            ->willReturn(4);

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $result = $this->service->calculateStageProgress($journey, 1);

        $this->assertEquals(75, $result);
    }

    public function test_can_advance_to_next_stage_when_above_80_percent(): void
    {
        $this->taskRepository
            ->expects($this->once())
            ->method('countCompletedByStage')
            ->willReturn(4);

        $this->taskRepository
            ->expects($this->once())
            ->method('countTotalByStage')
            ->willReturn(5);

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $this->assertTrue($this->service->canAdvanceToNextStage($journey));
    }

    public function test_can_advance_returns_false_below_80_percent(): void
    {
        $this->taskRepository
            ->expects($this->once())
            ->method('countCompletedByStage')
            ->willReturn(3);

        $this->taskRepository
            ->expects($this->once())
            ->method('countTotalByStage')
            ->willReturn(4);

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $this->assertFalse($this->service->canAdvanceToNextStage($journey));
    }

    public function test_can_advance_returns_false_when_no_tasks(): void
    {
        $this->taskRepository
            ->expects($this->once())
            ->method('countCompletedByStage')
            ->willReturn(0);

        $this->taskRepository
            ->expects($this->once())
            ->method('countTotalByStage')
            ->willReturn(0);

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $this->assertFalse($this->service->canAdvanceToNextStage($journey));
    }

    public function test_get_next_tasks_returns_pending_and_in_progress_tasks(): void
    {
        $userTask1 = $this->createUserTaskStub(TaskStatus::pending());
        $userTask2 = $this->createUserTaskStub(TaskStatus::inProgress());
        $userTask3 = $this->createUserTaskStub(TaskStatus::completed());

        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTasksByJourney')
            ->willReturn(new Collection([$userTask1, $userTask2, $userTask3]));

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $nextTasks = $this->service->getNextTasks($journey);

        $this->assertCount(2, $nextTasks);
    }

    public function test_get_next_tasks_respects_limit(): void
    {
        $tasks = [];
        for ($i = 0; $i < 10; $i++) {
            $tasks[] = $this->createUserTaskStub(TaskStatus::pending());
        }

        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTasksByJourney')
            ->willReturn(new Collection($tasks));

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $nextTasks = $this->service->getNextTasks($journey, 3);

        $this->assertCount(3, $nextTasks);
    }

    public function test_get_weekly_goals_returns_next_five_tasks(): void
    {
        $tasks = [];
        for ($i = 0; $i < 5; $i++) {
            $tasks[] = $this->createUserTaskStub(TaskStatus::pending());
        }

        $this->taskRepository
            ->expects($this->once())
            ->method('findUserTasksByJourney')
            ->willReturn(new Collection($tasks));

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $goals = $this->service->getWeeklyGoals($journey);

        $this->assertCount(5, $goals);
    }

    public function test_get_timeline_status_returns_expected_structure(): void
    {
        $stage = Stage::create('Idea', StageSlug::idea(), 1);

        $this->stageRepository
            ->expects($this->once())
            ->method('findActive')
            ->willReturn(new Collection([$stage]));

        $this->taskRepository
            ->expects($this->once())
            ->method('countCompletedByStage')
            ->willReturn(1);

        $this->taskRepository
            ->expects($this->once())
            ->method('countTotalByStage')
            ->willReturn(2);

        $journey = StartupJourney::create(1, 1, 1, 'Test', 1);
        $timeline = $this->service->getTimelineStatus($journey);

        $this->assertArrayHasKey('start_date', $timeline);
        $this->assertArrayHasKey('target_date', $timeline);
        $this->assertArrayHasKey('days_active', $timeline);
        $this->assertArrayHasKey('estimated_days_remaining', $timeline);
        $this->assertArrayHasKey('is_on_track', $timeline);
        $this->assertArrayHasKey('projected_completion_date', $timeline);
    }

    private function createUserTaskStub(TaskStatus $status): UserTask
    {
        $stub = $this->createStub(UserTask::class);
        $stub->method('getStatus')->willReturn($status);
        return $stub;
    }
}
