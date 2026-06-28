<?php

namespace App\Domain\Messaging\Services;

use App\Domain\Messaging\Entities\Message;
use App\Domain\Messaging\Events\MessageSent;
use App\Domain\Messaging\Events\MessageRead;
use App\Domain\Messaging\Repositories\MessageRepository;
use App\Domain\Messaging\ValueObjects\MessageContent;
use App\Domain\Messaging\ValueObjects\MessageId;
use App\Domain\Messaging\ValueObjects\UserId;
use Illuminate\Support\Facades\Event;

class MessagingService
{
    public function __construct(
        private MessageRepository $messageRepository
    ) {}

    public function sendMessage(
        UserId $senderId,
        UserId $recipientId,
        MessageContent $content,
        ?MessageId $parentId = null
    ): Message {
        // Create message without ID first (will be set by repository)
        $message = $this->messageRepository->create(
            $senderId,
            $recipientId,
            $content,
            $parentId
        );

        Event::dispatch(new MessageSent($message));

        return $message;
    }

    public function markAsRead(MessageId $messageId, UserId $userId): void
    {
        $message = $this->messageRepository->findById($messageId);

        if (!$message) {
            throw new \DomainException('Message not found');
        }

        if (!$message->canBeReadBy($userId)) {
            throw new \DomainException('User cannot read this message');
        }

        if (!$message->recipientId()->equals($userId)) {
            throw new \DomainException('Only recipient can mark message as read');
        }

        $message->markAsRead();
        $this->messageRepository->save($message);

        Event::dispatch(new MessageRead($message));
    }

    public function getConversation(UserId $user1, UserId $user2, int $limit = 50): array
    {
        return $this->messageRepository->findConversation($user1, $user2, $limit);
    }

    public function getInbox(UserId $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->messageRepository->findByRecipient($userId, $limit, $offset);
    }

    public function getSent(UserId $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->messageRepository->findBySender($userId, $limit, $offset);
    }

    public function getUnreadCount(UserId $userId): int
    {
        return $this->messageRepository->countUnreadByRecipient($userId);
    }

    public function canUserMessageRecipient(UserId $senderId, UserId $recipientId): bool
    {
        // Business rule: Users can message their upline, downline, or admins
        // This will be implemented based on your specific rules
        return true; // Placeholder
    }
}
