<?php

namespace App\Application\PrimeEdge\DTOs;

use App\Domain\PrimeEdge\Entities\ComplianceTask;

class ComplianceTaskDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $clientId,
        public readonly string $type,
        public readonly string $typeLabel,
        public readonly string $description,
        public readonly string $dueDate,
        public readonly string $recurrence,
        public readonly string $status,
        public readonly int $reminderDays,
        public readonly bool $isOverdue,
        public readonly int $daysUntilDue,
        public readonly ?string $notes,
        public readonly string $createdAt,
        public readonly ?string $completedAt,
    ) {}

    public static function fromEntity(ComplianceTask $task): self
    {
        return new self(
            id: $task->id()->toString(),
            clientId: $task->clientId()->toString(),
            type: $task->type()->value,
            typeLabel: $task->type()->label(),
            description: $task->description(),
            dueDate: $task->dueDate()->toString(),
            recurrence: $task->recurrence()->value,
            status: $task->status(),
            reminderDays: $task->reminderDays(),
            isOverdue: $task->isOverdue(),
            daysUntilDue: $task->dueDate()->daysUntil(),
            notes: $task->notes(),
            createdAt: $task->createdAt()->format('Y-m-d H:i:s'),
            completedAt: $task->completedAt()?->format('Y-m-d H:i:s'),
        );
    }
}
