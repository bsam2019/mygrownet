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
        // Use reflection to create entity with all fields from database
        $notification = new \ReflectionClass(Notification::class);
        $instance = $notification->newInstanceWithoutConstructor();
        
        // Set private properties
        $this->setProperty($instance, 'id', $model->id);
        $this->setProperty($instance, 'userId', $model->user_id);
        $this->setProperty($instance, 'type', NotificationType::fromString($model->type));
        $this->setProperty($instance, 'title', $model->title);
        $this->setProperty($instance, 'message', $model->message);
        $this->setProperty($instance, 'actionUrl', $model->action_url);
        $this->setProperty($instance, 'actionText', $model->action_text);
        $this->setProperty($instance, 'data', $model->data ?? []);
        $this->setProperty($instance, 'priority', NotificationPriority::from($model->priority));
        $this->setProperty($instance, 'readAt', $model->read_at ? new \DateTimeImmutable($model->read_at) : null);
        $this->setProperty($instance, 'archivedAt', $model->archived_at ? new \DateTimeImmutable($model->archived_at) : null);
        $this->setProperty($instance, 'createdAt', new \DateTimeImmutable($model->created_at));
        $this->setProperty($instance, 'expiresAt', $model->expires_at ? new \DateTimeImmutable($model->expires_at) : null);
        
        return $instance;
    }
    
    private function setProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }
}
