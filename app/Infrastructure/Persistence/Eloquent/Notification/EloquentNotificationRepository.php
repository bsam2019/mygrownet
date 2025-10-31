<?php

namespace App\Infrastructure\Persistence\Eloquent\Notification;

use App\Domain\Notification\Entities\Notification;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use App\Domain\Notification\ValueObjects\NotificationType;
use App\Domain\Notification\ValueObjects\NotificationPriority;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function save(Notification $notification): void
    {
        NotificationModel::create([
            'id' => $notification->id(),
            'user_id' => $notification->userId(),
            'type' => $notification->type()->value(),
            'category' => $notification->type()->category(),
            'title' => $notification->title(),
            'message' => $notification->message(),
            'action_url' => $notification->actionUrl(),
            'action_text' => $notification->actionText(),
            'data' => $notification->data(),
            'priority' => $notification->priority()->value,
            'read_at' => $notification->readAt(),
        ]);
    }

    public function findById(string $id): ?Notification
    {
        $model = NotificationModel::find($id);
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(int $userId, int $limit = 50): array
    {
        $models = NotificationModel::where('user_id', $userId)
            ->notArchived()
            ->latest()
            ->limit($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findUnreadByUserId(int $userId): array
    {
        $models = NotificationModel::where('user_id', $userId)
            ->unread()
            ->notArchived()
            ->latest()
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function countUnreadByUserId(int $userId): int
    {
        return NotificationModel::where('user_id', $userId)
            ->unread()
            ->notArchived()
            ->count();
    }

    public function markAsRead(string $id): void
    {
        NotificationModel::where('id', $id)->update([
            'read_at' => now()
        ]);
    }

    public function markAllAsRead(int $userId): void
    {
        NotificationModel::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function delete(string $id): void
    {
        NotificationModel::where('id', $id)->delete();
    }

    public function deleteExpired(): int
    {
        return NotificationModel::where('expires_at', '<', now())->delete();
    }

    private function toDomainEntity(NotificationModel $model): Notification
    {
        return Notification::create(
            id: $model->id,
            userId: $model->user_id,
            type: NotificationType::fromString($model->type),
            title: $model->title,
            message: $model->message,
            actionUrl: $model->action_url,
            actionText: $model->action_text,
            data: $model->data ?? [],
            priority: NotificationPriority::from($model->priority)
        );
    }
}
