<?php

declare(strict_types=1);

namespace App\Domain\GrowMart\Services;

use App\Infrastructure\Persistence\Eloquent\Notification\NotificationModel;
use App\Models\User;
use Illuminate\Support\Str;

class NotificationService
{
    private const MODULE = 'growmart';

    public function notify(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $actionText = null,
        string $category = 'orders',
        string $priority = 'normal',
        array $data = [],
    ): NotificationModel {
        return NotificationModel::create([
            'id' => Str::uuid()->toString(),
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => $type,
            'category' => $category,
            'module' => self::MODULE,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'data' => $data,
            'priority' => $priority,
            'created_at' => now(),
        ]);
    }
}
