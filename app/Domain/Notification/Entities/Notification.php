<?php

namespace App\Domain\Notification\Entities;

use App\Domain\Notification\ValueObjects\NotificationType;
use App\Domain\Notification\ValueObjects\NotificationPriority;
use DateTimeImmutable;

class Notification
{
    private function __construct(
        private readonly string $id,
        private readonly int $userId,
        private readonly NotificationType $type,
        private readonly string $title,
        private readonly string $message,
        private readonly ?string $actionUrl,
        private readonly ?string $actionText,
        private readonly array $data,
        private readonly NotificationPriority $priority,
        private ?DateTimeImmutable $readAt,
        private ?DateTimeImmutable $archivedAt,
        private readonly DateTimeImmutable $createdAt,
        private readonly ?DateTimeImmutable $expiresAt
    ) {}

    public static function create(
        string $id,
        int $userId,
        NotificationType $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $actionText = null,
        array $data = [],
        NotificationPriority $priority = NotificationPriority::NORMAL,
        ?DateTimeImmutable $expiresAt = null
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            type: $type,
            title: $title,
            message: $message,
            actionUrl: $actionUrl,
            actionText: $actionText,
            data: $data,
            priority: $priority,
            readAt: null,
            archivedAt: null,
            createdAt: new DateTimeImmutable(),
            expiresAt: $expiresAt
        );
    }

    public function markAsRead(): void
    {
        if ($this->readAt === null) {
            $this->readAt = new DateTimeImmutable();
        }
    }

    public function markAsUnread(): void
    {
        $this->readAt = null;
    }

    public function archive(): void
    {
        if ($this->archivedAt === null) {
            $this->archivedAt = new DateTimeImmutable();
        }
    }

    public function isRead(): bool
    {
        return $this->readAt !== null;
    }

    public function isArchived(): bool
    {
        return $this->archivedAt !== null;
    }

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt < new DateTimeImmutable();
    }

    // Getters
    public function id(): string
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function type(): NotificationType
    {
        return $this->type;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function actionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function actionText(): ?string
    {
        return $this->actionText;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function priority(): NotificationPriority
    {
        return $this->priority;
    }

    public function readAt(): ?DateTimeImmutable
    {
        return $this->readAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
