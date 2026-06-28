<?php

namespace App\Domain\Notification\Repositories;

use App\Domain\Notification\Entities\Notification;

interface NotificationRepositoryInterface
{
    public function save(Notification $notification): void;
    
    public function findById(string $id): ?Notification;
    
    public function findByUserId(int $userId, int $limit = 50): array;
    
    public function findUnreadByUserId(int $userId): array;
    
    public function countUnreadByUserId(int $userId): int;
    
    public function markAsRead(string $id): void;
    
    public function markAllAsRead(int $userId): void;
    
    public function delete(string $id): void;
    
    public function deleteExpired(): int;
}
