<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Services;

use App\Domain\GrowStart\Entities\StartupJourney;
use App\Domain\GrowStart\ValueObjects\JourneyProgress;
use App\Domain\GrowStart\Repositories\TaskRepositoryInterface;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;

class JourneyProgressService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private StageRepositoryInterface $stageRepository
    ) {}

    public function calculateProgress(StartupJourney $journey): JourneyProgress
    {
        $stages = $this->stageRepository->findActive();
        $stageProgress = [];
        $totalCompleted = 0;
        $totalTasks = 0;

        foreach ($stages as $stage) {
            $completed = $this->taskRepository->countCompletedByStage($journey->id(), $stage->getId());
            $total = $this->taskRepository->countTotalByStage($journey->id(), $stage->getId());
            
            $stageProgress[$stage->getId()] = [
                'stage_id' => $stage->getId(),
                'slug' => $stage->getSlug()->value(),
                'name' => $stage->getName(),
                'completed' => $completed,
                'total' => $total,
                'percentage' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            ];
            
            $totalCompleted += $completed;
            $totalTasks += $total;
        }

        $overallProgress = $totalTasks > 0 ? ($totalCompleted / $totalTasks) * 100 : 0;
        $daysActive = $journey->getDaysActive();
        
        // Estimate remaining days based on current pace
        $estimatedDaysRemaining = null;
        if ($totalCompleted > 0 && $daysActive > 0) {
            $tasksPerDay = $totalCompleted / $daysActive;
            $remainingTasks = $totalTasks - $totalCompleted;
            $estimatedDaysRemaining = $tasksPerDay > 0 
                ? (int) ceil($remainingTasks / $tasksPerDay) 
                : null;
        }

        return JourneyProgress::create(
            overall: $overallProgress,
            stageProgress: $stageProgress,
            tasksCompleted: $totalCompleted,
            totalTasks: $totalTasks,
            daysActive: $daysActive,
            estimatedDaysRemaining: $estimatedDaysRemaining
        );
    }

    public function calculateStageProgress(StartupJourney $journey, int $stageId): float
    {
        $completed = $this->taskRepository->countCompletedByStage($journey->id(), $stageId);
        $total = $this->taskRepository->countTotalByStage($journey->id(), $stageId);
        
        return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
    }

    public function canAdvanceToNextStage(StartupJourney $journey): bool
    {
        $currentStageId = $journey->getCurrentStageId();
        $completed = $this->taskRepository->countCompletedByStage($journey->id(), $currentStageId);
        $total = $this->taskRepository->countTotalByStage($journey->id(), $currentStageId);
        
        // Require at least 80% completion to advance
        return $total > 0 && ($completed / $total) >= 0.8;
    }

    public function getNextTasks(StartupJourney $journey, int $limit = 5): array
    {
        $userTasks = $this->taskRepository->findUserTasksByJourney($journey->id());
        
        $pendingTasks = $userTasks->filter(function ($userTask) {
            return $userTask->getStatus()->isPending() || $userTask->getStatus()->isInProgress();
        })->take($limit);

        return $pendingTasks->toArray();
    }

    public function getWeeklyGoals(StartupJourney $journey): array
    {
        // Get next 3-5 tasks as weekly goals
        return $this->getNextTasks($journey, 5);
    }

    public function getTimelineStatus(StartupJourney $journey): array
    {
        $progress = $this->calculateProgress($journey);
        $targetDate = $journey->getTargetLaunchDate();
        
        $status = [
            'start_date' => $journey->getStartedAt()->format('Y-m-d'),
            'target_date' => $targetDate?->format('Y-m-d'),
            'days_active' => $progress->daysActive(),
            'estimated_days_remaining' => $progress->estimatedDaysRemaining(),
            'is_on_track' => true,
            'projected_completion_date' => null,
        ];

        if ($progress->estimatedDaysRemaining() !== null) {
            $projectedDate = (new \DateTimeImmutable())
                ->modify("+{$progress->estimatedDaysRemaining()} days");
            $status['projected_completion_date'] = $projectedDate->format('Y-m-d');
            
            if ($targetDate) {
                $status['is_on_track'] = $projectedDate <= $targetDate;
            }
        }

        return $status;
    }
}
