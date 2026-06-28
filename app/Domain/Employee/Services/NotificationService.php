<?php

namespace App\Domain\Employee\Services;

use App\Events\Employee\EmployeeNotificationCreated;
use App\Events\Employee\TaskStatusUpdated;
use App\Events\Employee\TimeOffRequestUpdated;
use App\Models\Employee;
use App\Models\EmployeeNotification;
use App\Models\EmployeeTask;
use App\Models\EmployeeTimeOffRequest;

class NotificationService
{
    /**
     * Create a notification and broadcast it in real-time
     */
    public function createNotification(
        int $employeeId,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?array $data = null
    ): EmployeeNotification {
        $notification = EmployeeNotification::create([
            'employee_id' => $employeeId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);

        // Broadcast the notification in real-time
        broadcast(new EmployeeNotificationCreated($notification))->toOthers();

        return $notification;
    }

    /**
     * Notify about task assignment
     */
    public function notifyTaskAssigned(EmployeeTask $task): void
    {
        $assignee = Employee::find($task->assigned_to);
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

    /**
     * Notify about task status change
     */
    public function notifyTaskStatusChanged(EmployeeTask $task, string $oldStatus, string $newStatus): void
    {
        // Broadcast task status update
        broadcast(new TaskStatusUpdated($task, $oldStatus, $newStatus))->toOthers();

        // Notify the assigner if task is completed
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

    /**
     * Notify about time off request status change
     */
    public function notifyTimeOffStatusChanged(EmployeeTimeOffRequest $request, string $action): void
    {
        // Broadcast time off update
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

    /**
     * Notify about goal deadline approaching
     */
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

    /**
     * Notify about new announcement
     */
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

    /**
     * Mark notification as read
     */
    public function markAsRead(int $notificationId): bool
    {
        return EmployeeNotification::where('id', $notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]) > 0;
    }

    /**
     * Mark all notifications as read for an employee
     */
    public function markAllAsRead(int $employeeId): int
    {
        return EmployeeNotification::where('employee_id', $employeeId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount(int $employeeId): int
    {
        return EmployeeNotification::where('employee_id', $employeeId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get recent notifications for an employee
     */
    public function getRecentNotifications(int $employeeId, int $limit = 10): array
    {
        return EmployeeNotification::where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
