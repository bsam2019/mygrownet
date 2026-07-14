<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\Message;
use App\Domain\StockFlow\Exceptions\MessageNotFoundException;
use App\Domain\StockFlow\Repositories\MessageRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\MessageId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\UserId;

class MessagingService
{
    public function __construct(
        private MessageRepositoryInterface $messageRepository,
    ) {}

    public function sendMessage(int $companyId, int $senderId, int $recipientId, string $subject, string $body, ?int $parentId = null): Message
    {
        $message = Message::create(
            companyId: CompanyId::fromInt($companyId),
            senderId: UserId::fromInt($senderId),
            recipientId: UserId::fromInt($recipientId),
            subject: $subject,
            body: $body,
            parentId: $parentId ? MessageId::fromInt($parentId) : null,
        );

        return $this->messageRepository->save($message);
    }

    public function getInbox(int $companyId, int $userId): array
    {
        $messages = $this->messageRepository->findByRecipient(
            CompanyId::fromInt($companyId),
            UserId::fromInt($userId),
        );

        return array_map(fn(Message $m) => $m->toArray(), $messages);
    }

    public function getSent(int $companyId, int $userId): array
    {
        $messages = $this->messageRepository->findBySender(
            CompanyId::fromInt($companyId),
            UserId::fromInt($userId),
        );

        return array_map(fn(Message $m) => $m->toArray(), $messages);
    }

    public function getConversation(int $companyId, int $user1Id, int $user2Id): array
    {
        $messages = $this->messageRepository->findConversation(
            CompanyId::fromInt($companyId),
            UserId::fromInt($user1Id),
            UserId::fromInt($user2Id),
        );

        return array_map(fn(Message $m) => $m->toArray(), $messages);
    }

    public function markAsRead(int $messageId, int $userId): void
    {
        $message = $this->messageRepository->findById(MessageId::fromInt($messageId));

        if (!$message) {
            throw new MessageNotFoundException($messageId);
        }

        if (!$message->canBeReadBy(UserId::fromInt($userId))) {
            throw new \App\Domain\StockFlow\Exceptions\StockFlowException('You do not have permission to read this message.');
        }

        if (!$message->isRead()) {
            $message->markAsRead();
            $this->messageRepository->save($message);
        }
    }

    public function markAllAsRead(int $companyId, int $userId): void
    {
        $this->messageRepository->markAllAsRead(
            CompanyId::fromInt($companyId),
            UserId::fromInt($userId),
        );
    }

    public function getUnreadCount(int $companyId, int $userId): int
    {
        return $this->messageRepository->countUnreadByRecipient(
            CompanyId::fromInt($companyId),
            UserId::fromInt($userId),
        );
    }
}
