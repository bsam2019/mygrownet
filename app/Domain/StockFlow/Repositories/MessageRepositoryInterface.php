<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Repositories;

use App\Domain\StockFlow\Entities\Message;
use App\Domain\StockFlow\ValueObjects\MessageId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\UserId;

interface MessageRepositoryInterface
{
    public function findById(MessageId $id): ?Message;
    public function findBySender(CompanyId $companyId, UserId $senderId): array;
    public function findByRecipient(CompanyId $companyId, UserId $recipientId): array;
    public function findConversation(CompanyId $companyId, UserId $user1, UserId $user2): array;
    public function findReplies(MessageId $parentId): array;
    public function countUnreadByRecipient(CompanyId $companyId, UserId $recipientId): int;
    public function save(Message $message): Message;
    public function markAsRead(MessageId $id): void;
    public function markAllAsRead(CompanyId $companyId, UserId $recipientId): void;
}
