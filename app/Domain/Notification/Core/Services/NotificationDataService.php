<?php

namespace App\Domain\Notification\Core\Services;

use App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel;
use App\Models\User;
use Illuminate\Support\Str;

class NotificationDataService
{
    public function create(
        int $userId,
        string $type,
        string $title,
        string $message,
        string $module,
        ?string $actionUrl = null,
        ?string $actionText = null,
        string $category = 'info',
        string $priority = 'normal',
        array $data = []
    ): NotificationModel {
        return NotificationModel::create([
            'id' => Str::uuid()->toString(),
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'type' => $type,
            'module' => $module,
            'category' => $category,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'data' => $data,
            'priority' => $priority,
            'created_at' => now(),
        ]);
    }

    public function forUser(int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return NotificationModel::forUser($userId);
    }

    public function existsForUser(int $userId, string $module, string $category): bool
    {
        return NotificationModel::forUser($userId)
            ->where('module', $module)
            ->where('category', $category)
            ->exists();
    }
}
