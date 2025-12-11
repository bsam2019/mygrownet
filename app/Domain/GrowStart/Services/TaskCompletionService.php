<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Services;

use App\Domain\GrowStart\Entities\UserTask;
use App\Domain\GrowStart\Entities\StartupJourney;
use App\Domain\GrowStart\Repositories\TaskRepositoryInterface;
use App\Domain\GrowStart\Repositories\JourneyRepositoryInterface;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;
use App\Domain\GrowStart\Exceptions\TaskNotFoundException;
use App\Domain\GrowStart\Exceptions\JourneyNotFoundException;

class TaskCompletionService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private JourneyRepositoryInterface $journeyRepository,
        private StageRepositoryInterface $stageRepository,
        private JourneyProgressService $progressService
    ) {}

    public function completeTask(int $journeyId, int $taskId): UserTask
    {
        $userTask = $this->taskRepository->findUserTask($journeyId, $taskId);
        
        if (!$userTask) {
            // Create user task if it doesn't exist
            $userTask = UserTask::create($journeyId, $taskId);
        }
        
        $userTask->complete();
        $savedTask = $this->taskRepository->saveUserTask($userTask);
        
        // Check if we should auto-advance stage
        $this->checkStageAdvancement($journeyId);
        
        return $savedTask;
    }

    public function startTask(int $journeyId, int $taskId): UserTask
    {
        $userTask = $this->taskRepository->findUserTask($journeyId, $taskId);
        
        if (!$userTask) {
            $userTask = UserTask::create($journeyId, $taskId);
        }
        
        $userTask->start();
        return $this->taskRepository->saveUserTask($userTask);
    }

    public function skipTask(int $journeyId, int $taskId): UserTask
    {
        $userTask = $this->taskRepository->findUserTask($journeyId, $taskId);
        
        if (!$userTask) {
            $userTask = UserTask::create($journeyId, $taskId);
        }
        
        $userTask->skip();
        $savedTask = $this->taskRepository->saveUserTask($userTask);
        
        // Check if we should auto-advance stage
        $this->checkStageAdvancement($journeyId);
        
        return $savedTask;
    }

    public function resetTask(int $journeyId, int $taskId): UserTask
    {
        $userTask = $this->taskRepository->findUserTask($journeyId, $taskId);
        
        if (!$userTask) {
            throw new TaskNotFoundException("User task not found");
        }
        
        $userTask->reset();
        return $this->taskRepository->saveUserTask($userTask);
    }

    public function updateTaskNotes(int $journeyId, int $taskId, ?string $notes): UserTask
    {
        $userTask = $this->taskRepository->findUserTask($journeyId, $taskId);
        
        if (!$userTask) {
            $userTask = UserTask::create($journeyId, $taskId);
        }
        
        $userTask->updateNotes($notes);
        return $this->taskRepository->saveUserTask($userTask);
    }

    public function addTaskAttachment(int $journeyId, int $taskId, string $path): UserTask
    {
        $userTask = $this->taskRepository->findUserTask($journeyId, $taskId);
        
        if (!$userTask) {
            $userTask = UserTask::create($journeyId, $taskId);
        }
        
        $userTask->addAttachment($path);
        return $this->taskRepository->saveUserTask($userTask);
    }

    private function checkStageAdvancement(int $journeyId): void
    {
        $journey = $this->journeyRepository->findById($journeyId);
        
        if (!$journey || !$journey->getStatus()->isActive()) {
            return;
        }
        
        if ($this->progressService->canAdvanceToNextStage($journey)) {
            $nextStage = $this->stageRepository->findNext($journey->getCurrentStageId());
            
            if ($nextStage) {
                $journey->advanceToStage($nextStage->getId());
                $this->journeyRepository->save($journey);
            } else {
                // Last stage completed - mark journey as complete
                $journey->complete();
                $this->journeyRepository->save($journey);
            }
        }
    }
}
