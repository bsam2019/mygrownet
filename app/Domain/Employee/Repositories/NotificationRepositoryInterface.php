<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use Illuminate\Support\Collection;

interface NotificationRepositoryInterface
{
    public function findById(int $id): ?object;

    public function findByEmployee(int $employeeId): Collection;

    public function create(array $data): object;

    public function markAsRead(int $notificationId): bool;

    public function markAllAsRead(int $employeeId): int;

    public function getUnreadCount(int $employeeId): int;

    public function getRecent(int $employeeId, int $limit = 10): Collection;
}