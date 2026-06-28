<?php

namespace App\Application\Messaging\DTOs;

use App\Domain\Messaging\Entities\Message;

class MessageDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $senderId,
        public readonly string $senderName,
        public readonly int $recipientId,
        public readonly string $recipientName,
        public readonly string $subject,
        public readonly string $body,
        public readonly string $preview,
        public readonly bool $isRead,
        public readonly ?string $readAt,
        public readonly ?int $parentId,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {}

    public static function fromDomain(Message $message, string $senderName, string $recipientName): self
    {
        return new self(
            id: $message->id()->value(),
            senderId: $message->senderId()->value(),
            senderName: $senderName,
            recipientId: $message->recipientId()->value(),
            recipientName: $recipientName,
            subject: $message->content()->subject(),
            body: $message->content()->body(),
            preview: $message->content()->preview(),
            isRead: $message->isRead(),
            readAt: $message->readAt()?->format('Y-m-d H:i:s'),
            parentId: $message->parentId()?->value(),
            createdAt: $message->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $message->updatedAt()->format('Y-m-d H:i:s')
        );
    }
}
