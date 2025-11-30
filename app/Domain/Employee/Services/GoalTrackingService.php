<?php

declare(strict_types=1);

namespace App\Domain\Employee\Services;

use App\Domain\Employee\Entities\Goal;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Repositories\GoalRepositoryInterface;
use App\Domain\Employee\Exceptions\GoalException;
use DateTimeImmutable;
use Illuminate\Support\Collection;

class GoalTrackingService
{
    public function __construct(
        private GoalRepositoryInterface $goalRepository
    ) {}

    public function createGoal(
        EmployeeId $employeeId,
        string $title,
        string $category,
        DateTimeImmutable $startDate,
        DateTimeImmutable $dueDate,
        ?string $description = null,
        array $milestones = []
    ): Goal {
        $goal = Goal::create(
            $employeeId,
            $title,
            $category,
            $startDate,
            $dueDate,
            $description,
            $milestones
        );

        $this->goalRepository->save($goal);

        return $goal;
    }

    public function getGoalsForEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        return $this->goalRepository->findByEmployee($employeeId, $filters);
    }

    public function getActiveGoals(EmployeeId $employeeId): Collection
    {
        return $this->goalRepository->findActiveByEmployee($employeeId);
    }

    public function getGoalById(int $goalId): Goal
    {
        $goal = $this->goalRepository->findById($goalId);

        if (!$goal) {
            throw GoalException::goalNotFound($goalId);
        }

        return $goal;
    }

    public function updateProgress(int $goalId, int $progress): Goal
    {
        $goal = $this->getGoalById($goalId);
        
        $goal->updateProgress($progress);
        $this->goalRepository->save($goal);

        return $goal;
    }

    public function updateMilestone(int $goalId, int $milestoneIndex, bool $completed): Goal
    {
        $goal = $this->getGoalById($goalId);
        
        $goal->updateMilestone($milestoneIndex, $completed);
        $this->goalRepository->save($goal);

        return $goal;
    }

    public function addMilestone(int $goalId, string $title, ?DateTimeImmutable $targetDate = null): Goal
    {
        $goal = $this->getGoalById($goalId);
        
        $goal->addMilestone($title, $targetDate);
        $this->goalRepository->save($goal);

        return $goal;
    }

    public function completeGoal(int $goalId): Goal
    {
        $goal = $this->getGoalById($goalId);
        
        $goal->complete();
        $this->goalRepository->save($goal);

        return $goal;
    }

    public function cancelGoal(int $goalId): Goal
    {
        $goal = $this->getGoalById($goalId);
        
        $goal->cancel();
        $this->goalRepository->save($goal);

        return $goal;
    }

    public function updateGoalDetails(
        int $goalId,
        string $title,
        ?string $description,
        string $category,
        DateTimeImmutable $dueDate
    ): Goal {
        $goal = $this->getGoalById($goalId);
        
        $goal->updateDetails($title, $description, $category, $dueDate);
        $this->goalRepository->save($goal);

        return $goal;
    }

    public function getProgressStats(EmployeeId $employeeId): array
    {
        return $this->goalRepository->getProgressStats($employeeId);
    }

    public function getOverdueGoals(EmployeeId $employeeId): Collection
    {
        return $this->goalRepository->findOverdue($employeeId);
    }

    public function getCompletionRate(EmployeeId $employeeId, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): float
    {
        return $this->goalRepository->getCompletionRate($employeeId, $from, $to);
    }

    public function getGoalsSummary(EmployeeId $employeeId): array
    {
        $stats = $this->getProgressStats($employeeId);
        $active = $this->getActiveGoals($employeeId);
        $overdue = $this->getOverdueGoals($employeeId);

        return [
            'total_goals' => $stats['total'] ?? 0,
            'completed_goals' => $stats['completed'] ?? 0,
            'in_progress_goals' => $stats['in_progress'] ?? 0,
            'active_goals' => $active->count(),
            'overdue_goals' => $overdue->count(),
            'average_progress' => $stats['average_progress'] ?? 0,
            'completion_rate' => $stats['completion_rate'] ?? 0,
        ];
    }
}
