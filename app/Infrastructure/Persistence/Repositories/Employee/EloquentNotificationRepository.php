<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\Employee;

use App\Domain\Employee\Repositories\NotificationRepositoryInterface;
use App\Models\Employee\EmployeeNotification;
use Illuminate\Support\Collection;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function findById(int $id): ?EmployeeNotification
    {
        return EmployeeNotification::find($id);
    }

    public function findByEmployee(int $employeeId): Collection
    {
        return EmployeeNotification::where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): EmployeeNotification
    {
        return EmployeeNotification::create($data);
    }

    public function markAsRead(int $notificationId): bool
    {
        return EmployeeNotification::where('id', $notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]) > 0;
    }

    public function markAllAsRead(int $employeeId): int
    {
        return EmployeeNotification::where('employee_id', $employeeId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function getUnreadCount(int $employeeId): int
    {
        return EmployeeNotification::where('employee_id', $employeeId)
            ->whereNull('read_at')
            ->count();
    }

    public function getRecent(int $employeeId, int $limit = 10): Collection
    {
        return EmployeeNotification::where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}