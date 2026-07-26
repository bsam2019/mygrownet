<?php

namespace App\Domain\Core\Contracts;

interface NotificationProvider extends ProviderContract
{
    public function send(int $userId, string $type, string $title, string $message, ?string $actionUrl = null, ?string $actionText = null, string $category = 'general', string $priority = 'normal', array $data = []): array;

    public function getUnreadCount(int $userId): int;

    public function markAsRead(string $notificationId): void;
}
