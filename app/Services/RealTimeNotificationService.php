<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RealTimeNotificationService
{
    public function notifyEmployeeStatusChange(EmployeeModel $employee, string $oldStatus, string $newStatus): void
    {
        $notification = [
            'type' => 'employee_status_change',
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'timestamp' => now()->toISOString(),
            'department' => $employee->department?->name,
        ];

        // Broadcast to admin dashboard
        Broadcast::channel('admin-dashboard')->send('employee-update', $notification);

        // Notify department heads
        if ($employee->department) {
            $departmentHead = $employee->department->headEmployee;
            if ($departmentHead && $departmentHead->user) {
                $this->sendUserNotification($departmentHead->user, $notification);
            }
        }

        // Notify HR managers
        $hrManagers = User::role('hr-manager')->get();
        foreach ($hrManagers as $manager) {
            $this->sendUserNotification($manager, $notification);
        }

        // Log for audit trail
        Log::info('Employee status change notification sent', $notification);
    }

    public function notifyPerformanceReviewDue(EmployeeModel $employee): void
    {
        $notification = [
            'type' => 'performance_review_due',
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'due_date' => now()->addDays(14)->toDateString(),
            'last_review' => $employee->lastPerformanceReview?->period_end?->toDateString(),
            'manager' => $employee->manager?->full_name,
            'timestamp' => now()->toISOString(),
        ];

        // Notify the employee's manager
        if ($employee->manager && $employee->manager->user) {
            $this->sendUserNotification($employee->manager->user, $notification);
        }

        // Notify HR
        $hrManagers = User::role('hr-manager')->get();
        foreach ($hrManagers as $manager) {
            $this->sendUserNotification($manager, $notification);
        }

        // Add to dashboard alerts
        $this->addDashboardAlert('performance_review_due', $notification);
    }

    public function notifyCommissionCalculated(EmployeeModel $employee, float $amount, string $type): void
    {
        $notification = [
            'type' => 'commission_calculated',
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'commission_amount' => $amount,
            'commission_type' => $type,
            'timestamp' => now()->toISOString(),
        ];

        // Notify the employee if they have a user account
        if ($employee->user) {
            $this->sendUserNotification($employee->user, $notification);
        }

        // Notify payroll administrators
        $payrollAdmins = User::permission('process-payroll')->get();
        foreach ($payrollAdmins as $admin) {
            $this->sendUserNotification($admin, $notification);
        }

        // Update dashboard metrics
        $this->updateDashboardMetrics('commission_calculated', $amount);
    }

    public function notifyBirthdayReminder(EmployeeModel $employee): void
    {
        $notification = [
            'type' => 'employee_birthday',
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'birthday' => $employee->date_of_birth?->format('M d'),
            'department' => $employee->department?->name,
            'years_with_company' => $employee->years_of_service,
            'timestamp' => now()->toISOString(),
        ];

        // Notify department colleagues
        $colleagues = EmployeeModel::where('department_id', $employee->department_id)
            ->where('id', '!=', $employee->id)
            ->where('employment_status', 'active')
            ->with('user')
            ->get();

        foreach ($colleagues as $colleague) {
            if ($colleague->user) {
                $this->sendUserNotification($colleague->user, $notification);
            }
        }

        // Notify HR for celebration planning
        $hrManagers = User::role('hr-manager')->get();
        foreach ($hrManagers as $manager) {
            $this->sendUserNotification($manager, $notification);
        }
    }

    public function notifyWorkAnniversary(EmployeeModel $employee, int $years): void
    {
        $notification = [
            'type' => 'work_anniversary',
            'employee_id' => $employee->id,
            'employee_name' => $employee->full_name,
            'years_of_service' => $years,
            'hire_date' => $employee->hire_date->format('M d, Y'),
            'department' => $employee->department?->name,
            'position' => $employee->position?->title,
            'timestamp' => now()->toISOString(),
        ];

        // Notify the employee
        if ($employee->user) {
            $this->sendUserNotification($employee->user, $notification);
        }

        // Notify management
        $managers = User::role(['hr-manager', 'department-head', 'manager'])->get();
        foreach ($managers as $manager) {
            $this->sendUserNotification($manager, $notification);
        }

        // Add to company-wide announcements
        Broadcast::channel('company-announcements')->send('work-anniversary', $notification);
    }

    private function sendUserNotification(User $user, array $notification): void
    {
        // Send real-time notification via WebSocket
        Broadcast::channel("user.{$user->id}")->send('notification', $notification);

        // Store in database for persistence
        $user->notifications()->create([
            'type' => $notification['type'],
            'data' => $notification,
            'read_at' => null,
        ]);

        // Update user's notification count cache
        $cacheKey = "user_notifications_count_{$user->id}";
        Cache::increment($cacheKey);
    }

    private function addDashboardAlert(string $type, array $data): void
    {
        $alerts = Cache::get('dashboard_alerts', []);
        $alerts[] = [
            'id' => uniqid(),
            'type' => $type,
            'data' => $data,
            'created_at' => now()->toISOString(),
            'severity' => $this->getAlertSeverity($type),
        ];

        // Keep only last 50 alerts
        $alerts = array_slice($alerts, -50);
        
        Cache::put('dashboard_alerts', $alerts, now()->addHours(24));
    }

    private function updateDashboardMetrics(string $metric, $value): void
    {
        $cacheKey = "dashboard_metric_{$metric}";
        $current = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $current + $value, now()->addHours(1));
    }

    private function getAlertSeverity(string $type): string
    {
        return match ($type) {
            'performance_review_due' => 'warning',
            'employee_status_change' => 'info',
            'commission_calculated' => 'success',
            'employee_birthday' => 'info',
            'work_anniversary' => 'info',
            default => 'info',
        };
    }
}