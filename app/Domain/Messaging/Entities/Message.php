<?php

namespace App\Domain\Messaging\Entities;

use App\Domain\Messaging\ValueObjects\MessageId;
use App\Domain\Messaging\ValueObjects\MessageContent;
use App\Domain\Messaging\ValueObjects\UserId;
use DateTimeImmutable;

class Message
{
    private function __construct(
        private MessageId $id,
        private UserId $senderId,
        private UserId $recipientId,
        private MessageContent $content,
        private bool $isRead,
        private ?DateTimeImmutable $readAt,
        private ?MessageId $parentId,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        MessageId $id,
        UserId $senderId,
        UserId $recipientId,
        MessageContent $content,
        ?MessageId $parentId = null
    ): self {
        return new self(
            id: $id,
            senderId: $senderId,
            recipientId: $recipientId,
            content: $content,
            isRead: false,
            readAt: null,
            parentId: $parentId,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public static function reconstitute(
        MessageId $id,
        UserId $senderId,
        UserId $recipientId,
        MessageContent $content,
        bool $isRead,
        ?DateTimeImmutable $readAt,
        ?MessageId $parentId,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            $id,
            $senderId,
            $recipientId,
            $content,
            $isRead,
            $readAt,
            $parentId,
            $createdAt,
            $updatedAt
        );
    }

    public function markAsRead(): void
    {
        if ($this->isRead) {
            return;
        }

        $this->isRead = true;
        $this->readAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isReply(): bool
    {
        return $this->parentId !== null;
    }

    public function canBeReadBy(UserId $userId): bool
    {
        return $this->recipientId->equals($userId) || $this->senderId->equals($userId);
    }

    // Getters
    public function id(): MessageId
    {
        return $this->id;
    }

    public function senderId(): UserId
    {
        return $this->senderId;
    }

    public function recipientId(): UserId
    {
        return $this->recipientId;
    }

    public function content(): MessageContent
    {
        return $this->content;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function readAt(): ?DateTimeImmutable
    {
        return $this->readAt;
    }

    public function parentId(): ?MessageId
    {
        return $this->parentId;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
