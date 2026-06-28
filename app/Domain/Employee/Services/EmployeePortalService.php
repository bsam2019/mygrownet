<?php

declare(strict_types=1);

namespace App\Domain\Employee\Services;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\Exceptions\EmployeeNotFoundException;
use DateTimeImmutable;

class EmployeePortalService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private TaskManagementService $taskService,
        private GoalTrackingService $goalService,
        private TimeOffService $timeOffService,
        private AttendanceService $attendanceService
    ) {}

    public function getDashboardData(int $userId): array
    {
        $employee = $this->employeeRepository->findByUserId($userId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException("Employee not found for user: {$userId}");
        }

        $employeeId = EmployeeId::fromInt($employee->getId()->toInt());
        $now = new DateTimeImmutable();
        $year = (int) $now->format('Y');

        return [
            'employee' => $this->formatEmployeeData($employee),
            'tasks' => $this->taskService->getProductivityMetrics($employeeId),
            'goals' => $this->goalService->getGoalsSummary($employeeId),
            'time_off' => $this->timeOffService->getTimeOffSummary($employeeId, $year),
            'attendance' => $this->attendanceService->getAttendanceSummary($employeeId),
            'recent_tasks' => $this->taskService->getUpcomingTasks($employeeId, 7)->take(5),
            'active_goals' => $this->goalService->getActiveGoals($employeeId)->take(5),
        ];
    }

    public function getEmployeeProfile(int $userId): array
    {
        $employee = $this->employeeRepository->findByUserId($userId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException("Employee not found for user: {$userId}");
        }

        return [
            'employee' => $this->formatEmployeeData($employee),
            'department' => [
                'id' => $employee->getDepartment()->getId()->toInt(),
                'name' => $employee->getDepartment()->getName(),
            ],
            'position' => [
                'id' => $employee->getPosition()->getId()->toInt(),
                'title' => $employee->getPosition()->getTitle(),
            ],
            'manager' => $employee->getManager() ? [
                'id' => $employee->getManager()->getId()->toInt(),
                'name' => $employee->getManager()->getFullName(),
            ] : null,
            'years_of_service' => $employee->getYearsOfService(),
            'hire_date' => $employee->getHireDate()->format('Y-m-d'),
        ];
    }

    public function getTeamMembers(int $userId): array
    {
        $employee = $this->employeeRepository->findByUserId($userId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException("Employee not found for user: {$userId}");
        }

        // Get team members in the same department
        $teamMembers = $this->employeeRepository->findByDepartment(
            $employee->getDepartment()->getId()->toInt()
        );

        return $teamMembers->map(function ($member) use ($employee) {
            return [
                'id' => $member->getId()->toInt(),
                'name' => $member->getFullName(),
                'position' => $member->getPosition()->getTitle(),
                'email' => $member->getEmail()->getValue(),
                'phone' => $member->getPhone()?->getValue(),
                'is_manager' => $member->getId()->equals($employee->getManager()?->getId()),
                'is_self' => $member->getId()->equals($employee->getId()),
            ];
        })->toArray();
    }

    public function getNotifications(int $userId, int $limit = 20): array
    {
        $employee = $this->employeeRepository->findByUserId($userId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException("Employee not found for user: {$userId}");
        }

        // This would typically come from a notification repository
        // For now, we'll return an empty array
        return [];
    }

    public function getPerformanceSummary(int $userId): array
    {
        $employee = $this->employeeRepository->findByUserId($userId);
        
        if (!$employee) {
            throw new EmployeeNotFoundException("Employee not found for user: {$userId}");
        }

        $employeeId = EmployeeId::fromInt($employee->getId()->toInt());
        $metrics = $employee->getLastPerformanceMetrics();

        return [
            'overall_score' => $metrics?->calculateOverallScore() ?? 0,
            'last_review_date' => $employee->getLastPerformanceReview()?->format('Y-m-d'),
            'task_metrics' => $this->taskService->getProductivityMetrics($employeeId),
            'goal_metrics' => $this->goalService->getGoalsSummary($employeeId),
            'performance_metrics' => $metrics ? [
                'investments_facilitated' => $metrics->getInvestmentsFacilitated(),
                'client_retention_rate' => $metrics->getClientRetentionRate(),
                'commission_generated' => $metrics->getCommissionGenerated(),
                'new_client_acquisitions' => $metrics->getNewClientAcquisitions(),
                'goal_achievement_rate' => $metrics->getGoalAchievementRate(),
            ] : null,
        ];
    }

    private function formatEmployeeData($employee): array
    {
        return [
            'id' => $employee->getId()->toInt(),
            'employee_number' => $employee->getEmployeeNumber(),
            'first_name' => $employee->getFirstName(),
            'last_name' => $employee->getLastName(),
            'full_name' => $employee->getFullName(),
            'email' => $employee->getEmail()->getValue(),
            'phone' => $employee->getPhone()?->getValue(),
            'status' => $employee->getStatus()->getStatus(),
            'hire_date' => $employee->getHireDate()->format('Y-m-d'),
        ];
    }
}
