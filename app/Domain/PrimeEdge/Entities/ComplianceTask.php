<?php

namespace App\Domain\PrimeEdge\Entities;

use App\Domain\PrimeEdge\ValueObjects\ComplianceTaskId;
use App\Domain\PrimeEdge\ValueObjects\ComplianceType;
use App\Domain\PrimeEdge\ValueObjects\TaskRecurrence;
use App\Domain\PrimeEdge\ValueObjects\Deadline;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use DateTimeImmutable;

class ComplianceTask
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_MISSED = 'missed';
    public const STATUS_WAIVED = 'waived';

    private function __construct(
        private readonly ComplianceTaskId $id,
        private readonly ClientId $clientId,
        private ComplianceType $type,
        private string $description,
        private Deadline $dueDate,
        private TaskRecurrence $recurrence,
        private string $status,
        private int $reminderDays,
        private ?string $notes,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $completedAt,
        private ?DateTimeImmutable $remindedAt,
        private ?DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        ComplianceTaskId $id,
        ClientId $clientId,
        ComplianceType $type,
        string $description,
        Deadline $dueDate,
        TaskRecurrence $recurrence = TaskRecurrence::ONE_OFF,
        int $reminderDays = 7,
        ?string $notes = null,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            type: $type,
            description: $description,
            dueDate: $dueDate,
            recurrence: $recurrence,
            status: self::STATUS_PENDING,
            reminderDays: $reminderDays,
            notes: $notes,
            createdAt: new DateTimeImmutable(),
            completedAt: null,
            remindedAt: null,
            updatedAt: null,
        );
    }

    public static function reconstitute(
        ComplianceTaskId $id,
        ClientId $clientId,
        ComplianceType $type,
        string $description,
        Deadline $dueDate,
        TaskRecurrence $recurrence,
        string $status,
        int $reminderDays,
        ?string $notes,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $completedAt,
        ?DateTimeImmutable $remindedAt,
        ?DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            clientId: $clientId,
            type: $type,
            description: $description,
            dueDate: $dueDate,
            recurrence: $recurrence,
            status: $status,
            reminderDays: $reminderDays,
            notes: $notes,
            createdAt: $createdAt,
            completedAt: $completedAt,
            remindedAt: $remindedAt,
            updatedAt: $updatedAt,
        );
    }

    public function markAsCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsMissed(): void
    {
        $this->status = self::STATUS_MISSED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsWaived(?string $reason = null): void
    {
        $this->status = self::STATUS_WAIVED;
        $this->notes = $reason;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markReminded(): void
    {
        $this->remindedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->dueDate->isOverdue();
    }

    public function isUpcomingForReminder(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->dueDate->isWithinDays($this->reminderDays);
    }

    public function id(): ComplianceTaskId { return $this->id; }
    public function clientId(): ClientId { return $this->clientId; }
    public function type(): ComplianceType { return $this->type; }
    public function description(): string { return $this->description; }
    public function dueDate(): Deadline { return $this->dueDate; }
    public function recurrence(): TaskRecurrence { return $this->recurrence; }
    public function status(): string { return $this->status; }
    public function reminderDays(): int { return $this->reminderDays; }
    public function notes(): ?string { return $this->notes; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function completedAt(): ?DateTimeImmutable { return $this->completedAt; }
    public function remindedAt(): ?DateTimeImmutable { return $this->remindedAt; }
    public function updatedAt(): ?DateTimeImmutable { return $this->updatedAt; }
}
