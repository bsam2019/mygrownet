<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\MessageId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\UserId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class Message implements Arrayable
{
    private function __construct(
        private MessageId $id,
        private CompanyId $companyId,
        private UserId $senderId,
        private UserId $recipientId,
        private string $subject,
        private string $body,
        private bool $isRead,
        private ?DateTimeImmutable $readAt,
        private ?MessageId $parentId,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        CompanyId $companyId,
        UserId $senderId,
        UserId $recipientId,
        string $subject,
        string $body,
        ?MessageId $parentId = null,
    ): self {
        return new self(
            id: MessageId::generate(),
            companyId: $companyId,
            senderId: $senderId,
            recipientId: $recipientId,
            subject: $subject,
            body: $body,
            isRead: false,
            readAt: null,
            parentId: $parentId,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        MessageId $id,
        CompanyId $companyId,
        UserId $senderId,
        UserId $recipientId,
        string $subject,
        string $body,
        bool $isRead,
        ?DateTimeImmutable $readAt,
        ?MessageId $parentId,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $senderId, $recipientId, $subject, $body, $isRead, $readAt, $parentId, $createdAt, $updatedAt);
    }

    public function markAsRead(): void
    {
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
        return $this->senderId->equals($userId) || $this->recipientId->equals($userId);
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): MessageId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getSenderId(): UserId { return $this->senderId; }
    public function getSenderIdValue(): int { return $this->senderId->toInt(); }
    public function getRecipientId(): UserId { return $this->recipientId; }
    public function getRecipientIdValue(): int { return $this->recipientId->toInt(); }
    public function getSubject(): string { return $this->subject; }
    public function getBody(): string { return $this->body; }
    public function isRead(): bool { return $this->isRead; }
    public function getReadAt(): ?DateTimeImmutable { return $this->readAt; }
    public function getParentId(): ?MessageId { return $this->parentId; }
    public function getParentIdValue(): ?int { return $this->parentId?->toInt(); }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'sender_id' => $this->senderId->toInt(),
            'recipient_id' => $this->recipientId->toInt(),
            'subject' => $this->subject,
            'body' => $this->body,
            'is_read' => $this->isRead,
            'read_at' => $this->readAt?->format('Y-m-d H:i:s'),
            'parent_id' => $this->parentId?->toInt(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
