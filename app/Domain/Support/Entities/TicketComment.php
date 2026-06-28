<?php

namespace App\Domain\Support\Entities;

use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\UserId;
use DateTimeImmutable;

class TicketComment
{
    private function __construct(
        private int $id,
        private TicketId $ticketId,
        private UserId $userId,
        private string $comment,
        private bool $isInternal,
        private DateTimeImmutable $createdAt
    ) {}

    public static function create(
        int $id,
        TicketId $ticketId,
        UserId $userId,
        string $comment,
        bool $isInternal = false
    ): self {
        $trimmed = trim($comment);
        
        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Comment cannot be empty');
        }

        if (strlen($trimmed) > 2000) {
            throw new \InvalidArgumentException('Comment cannot exceed 2000 characters');
        }

        return new self(
            id: $id,
            ticketId: $ticketId,
            userId: $userId,
            comment: $trimmed,
            isInternal: $isInternal,
            createdAt: new DateTimeImmutable()
        );
    }

    public function id(): int { return $this->id; }
    public function ticketId(): TicketId { return $this->ticketId; }
    public function userId(): UserId { return $this->userId; }
    public function comment(): string { return $this->comment; }
    public function isInternal(): bool { return $this->isInternal; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
}
