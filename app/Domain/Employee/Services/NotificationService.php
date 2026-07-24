<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\Repositories\NotificationRepositoryInterface;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Events\Employee\EmployeeNotificationCreated;
use App\Events\Employee\TaskStatusUpdated;
use App\Events\Employee\TimeOffRequestUpdated;

class NotificationService
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepo,
        private EmployeeRepositoryInterface $employeeRepo
    ) {}

    public function createNotification(
        int $employeeId,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?array $data = null
    ): object {
        $notification = $this->notificationRepo->create([
            'employee_id' => $employeeId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);

        broadcast(new EmployeeNotificationCreated($notification))->toOthers();

        return $notification;
    }

    public function notifyTaskAssigned(object $task): void
    {
        $assignee = $this->employeeRepo->findById(EmployeeId::fromInt($task->assigned_to));
        if (!$assignee) return;

        $this->createNotification(
            $task->assigned_to,
            'task_assigned',
            'New Task Assigned',
            "You have been assigned a new task: {$task->title}",
            "/employee/portal/tasks/{$task->id}",
            ['task_id' => $task->id]
        );
    }

    public function notifyTaskStatusChanged(object $task, string $oldStatus, string $newStatus): void
    {
        broadcast(new TaskStatusUpdated($task, $oldStatus, $newStatus))->toOthers();

        if ($newStatus === 'completed' && $task->assigned_by && $task->assigned_by !== $task->assigned_to) {
            $this->createNotification(
                $task->assigned_by,
                'task_completed',
                'Task Completed',
                "Task '{$task->title}' has been completed",
                "/employee/portal/tasks/{$task->id}",
                ['task_id' => $task->id]
            );
        }
    }

    public function notifyTimeOffStatusChanged(object $request, string $action): void
    {
        broadcast(new TimeOffRequestUpdated($request, $action))->toOthers();

        $statusMessages = [
            'approved' => 'Your time off request has been approved',
            'rejected' => 'Your time off request has been rejected',
            'cancelled' => 'Your time off request has been cancelled',
        ];

        $this->createNotification(
            $request->employee_id,
            'time_off_' . $action,
            'Time Off ' . ucfirst($action),
            $statusMessages[$action] ?? "Your time off request status has changed to {$action}",
            '/employee/portal/time-off',
            ['request_id' => $request->id]
        );
    }

    public function notifyGoalDeadlineApproaching(int $employeeId, int $goalId, string $goalTitle, int $daysRemaining): void
    {
        $this->createNotification(
            $employeeId,
            'goal_reminder',
            'Goal Deadline Approaching',
            "Your goal '{$goalTitle}' is due in {$daysRemaining} days",
            '/employee/portal/goals',
            ['goal_id' => $goalId, 'days_remaining' => $daysRemaining]
        );
    }

    public function notifyNewAnnouncement(int $employeeId, int $announcementId, string $title): void
    {
        $this->createNotification(
            $employeeId,
            'announcement',
            'New Announcement',
            $title,
            "/employee/portal/announcements/{$announcementId}",
            ['announcement_id' => $announcementId]
        );
    }

    public function markAsRead(int $notificationId): bool
    {
        return $this->notificationRepo->markAsRead($notificationId);
    }

    public function markAllAsRead(int $employeeId): int
    {
        return $this->notificationRepo->markAllAsRead($employeeId);
    }

    public function getUnreadCount(int $employeeId): int
    {
        return $this->notificationRepo->getUnreadCount($employeeId);
    }

    public function getRecentNotifications(int $employeeId, int $limit = 10): array
    {
        return $this->notificationRepo->getRecent($employeeId, $limit)
            ->toArray();
    }
}