<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Entities;

use App\Domain\GrowStart\ValueObjects\TaskStatus;
use DateTimeImmutable;

/**
 * GrowStart User Task Entity
 * 
 * Represents a user's progress on a specific task.
 */
class UserTask
{
    private function __construct(
        private int $id,
        private int $userJourneyId,
        private int $taskId,
        private TaskStatus $status,
        private ?DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $completedAt,
        private ?string $notes,
        private array $attachments,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $userJourneyId,
        int $taskId
    ): self {
        $now = new DateTimeImmutable();
        
        return new self(
            id: 0,
            userJourneyId: $userJourneyId,
            taskId: $taskId,
            status: TaskStatus::pending(),
            startedAt: null,
            completedAt: null,
            notes: null,
            attachments: [],
            createdAt: $now,
            updatedAt: $now
        );
    }

    public static function reconstitute(
        int $id,
        int $userJourneyId,
        int $taskId,
        TaskStatus $status,
        ?DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt,
        ?string $notes,
        array $attachments,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            $id, $userJourneyId, $taskId, $status, $startedAt, $completedAt,
            $notes, $attachments, $createdAt, $updatedAt
        );
    }

    public function start(): void
    {
        if (!$this->status->isPending()) {
            return;
        }
        
        $this->status = TaskStatus::inProgress();
        $this->startedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void
    {
        $this->status = TaskStatus::completed();
        $this->completedAt = new DateTimeImmutable();
        
        if ($this->startedAt === null) {
            $this->startedAt = $this->completedAt;
        }
        
        $this->updatedAt = new DateTimeImmutable();
    }

    public function skip(): void
    {
        $this->status = TaskStatus::skipped();
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reset(): void
    {
        $this->status = TaskStatus::pending();
        $this->startedAt = null;
        $this->completedAt = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateNotes(?string $notes): void
    {
        $this->notes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addAttachment(string $path): void
    {
        $this->attachments[] = $path;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function removeAttachment(string $path): void
    {
        $this->attachments = array_values(array_filter(
            $this->attachments,
            fn($a) => $a !== $path
        ));
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getDurationInHours(): ?float
    {
        if (!$this->startedAt || !$this->completedAt) {
            return null;
        }
        
        $diff = $this->startedAt->diff($this->completedAt);
        return round($diff->h + ($diff->days * 24) + ($diff->i / 60), 2);
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUserJourneyId(): int { return $this->userJourneyId; }
    public function getTaskId(): int { return $this->taskId; }
    public function getStatus(): TaskStatus { return $this->status; }
    public function getStartedAt(): ?DateTimeImmutable { return $this->startedAt; }
    public function getCompletedAt(): ?DateTimeImmutable { return $this->completedAt; }
    public function getNotes(): ?string { return $this->notes; }
    public function getAttachments(): array { return $this->attachments; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_journey_id' => $this->userJourneyId,
            'task_id' => $this->taskId,
            'status' => $this->status->value(),
            'started_at' => $this->startedAt?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'attachments' => $this->attachments,
            'duration_hours' => $this->getDurationInHours(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
