<?php

namespace App\Domain\Support\Entities;

use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\UserId;
use App\Domain\Support\ValueObjects\TicketCategory;
use App\Domain\Support\ValueObjects\TicketPriority;
use App\Domain\Support\ValueObjects\TicketStatus;
use App\Domain\Support\ValueObjects\TicketContent;
use DateTimeImmutable;

class Ticket
{
    private function __construct(
        private TicketId $id,
        private UserId $userId,
        private TicketCategory $category,
        private TicketPriority $priority,
        private TicketStatus $status,
        private string $subject,
        private TicketContent $description,
        private ?UserId $assignedTo,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $resolvedAt = null,
        private ?DateTimeImmutable $closedAt = null,
        private ?int $satisfactionRating = null
    ) {}

    public static function create(
        TicketId $id,
        UserId $userId,
        TicketCategory $category,
        TicketPriority $priority,
        string $subject,
        TicketContent $description
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            category: $category,
            priority: $priority,
            status: TicketStatus::open(),
            subject: $subject,
            description: $description,
            assignedTo: null,
            createdAt: new DateTimeImmutable()
        );
    }

    public function assignTo(UserId $adminId): void
    {
        if ($this->status->isClosed()) {
            throw new \DomainException('Cannot assign a closed ticket');
        }

        $this->assignedTo = $adminId;
        
        if ($this->status->isOpen()) {
            $this->status = TicketStatus::inProgress();
        }
    }

    public function updateStatus(TicketStatus $newStatus): void
    {
        if ($this->status->isClosed() && !$newStatus->isOpen()) {
            throw new \DomainException('Cannot change status of a closed ticket except to reopen');
        }

        $this->status = $newStatus;

        if ($newStatus->isResolved()) {
            $this->resolvedAt = new DateTimeImmutable();
        }

        if ($newStatus->isClosed()) {
            $this->closedAt = new DateTimeImmutable();
        }
    }

    public function resolve(): void
    {
        $this->updateStatus(TicketStatus::resolved());
    }

    public function close(): void
    {
        if (!$this->status->isResolved()) {
            throw new \DomainException('Ticket must be resolved before closing');
        }
        
        $this->updateStatus(TicketStatus::closed());
    }

    public function reopen(): void
    {
        if (!$this->status->isClosed() && !$this->status->isResolved()) {
            throw new \DomainException('Can only reopen resolved or closed tickets');
        }

        $this->status = TicketStatus::open();
        $this->resolvedAt = null;
        $this->closedAt = null;
    }

    public function rate(int $rating): void
    {
        if (!$this->status->isResolved() && !$this->status->isClosed()) {
            throw new \DomainException('Can only rate resolved or closed tickets');
        }

        if ($rating < 1 || $rating > 5) {
            throw new \DomainException('Rating must be between 1 and 5');
        }

        $this->satisfactionRating = $rating;
    }

    public function changePriority(TicketPriority $newPriority): void
    {
        if ($this->status->isClosed()) {
            throw new \DomainException('Cannot change priority of a closed ticket');
        }

        $this->priority = $newPriority;
    }

    public function isAssigned(): bool
    {
        return $this->assignedTo !== null;
    }

    public function isOverdue(int $hoursThreshold): bool
    {
        if ($this->status->isClosed() || $this->status->isResolved()) {
            return false;
        }

        $now = new DateTimeImmutable();
        $hoursSinceCreation = ($now->getTimestamp() - $this->createdAt->getTimestamp()) / 3600;

        return $hoursSinceCreation > $hoursThreshold;
    }

    // Getters
    public function id(): TicketId { return $this->id; }
    public function userId(): UserId { return $this->userId; }
    public function category(): TicketCategory { return $this->category; }
    public function priority(): TicketPriority { return $this->priority; }
    public function status(): TicketStatus { return $this->status; }
    public function subject(): string { return $this->subject; }
    public function description(): TicketContent { return $this->description; }
    public function assignedTo(): ?UserId { return $this->assignedTo; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function resolvedAt(): ?DateTimeImmutable { return $this->resolvedAt; }
    public function closedAt(): ?DateTimeImmutable { return $this->closedAt; }
    public function satisfactionRating(): ?int { return $this->satisfactionRating; }
}