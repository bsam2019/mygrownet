<?php

namespace App\Domain\Messaging\Repositories;

use App\Domain\Messaging\Entities\Message;
use App\Domain\Messaging\ValueObjects\MessageContent;
use App\Domain\Messaging\ValueObjects\MessageId;
use App\Domain\Messaging\ValueObjects\UserId;

interface MessageRepository
{
    public function create(
        UserId $senderId,
        UserId $recipientId,
        MessageContent $content,
        ?MessageId $parentId = null
    ): Message;

    public function save(Message $message): void;

    public function findById(MessageId $id): ?Message;

    public function findByRecipient(UserId $recipientId, int $limit = 50, int $offset = 0): array;

    public function findBySender(UserId $senderId, int $limit = 50, int $offset = 0): array;

    public function findConversation(UserId $user1, UserId $user2, int $limit = 50): array;

    public function findReplies(MessageId $parentId): array;

    public function countUnreadByRecipient(UserId $recipientId): int;

    public function markAsRead(MessageId $id): void;

    public function delete(MessageId $id): void;
}
