<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Entities;

use App\Domain\GrowBiz\ValueObjects\TaskId;
use App\Domain\GrowBiz\ValueObjects\TaskStatus;
use App\Domain\GrowBiz\ValueObjects\TaskPriority;
use DateTimeImmutable;

/**
 * GrowBiz Task Entity
 * 
 * Represents a task created by an SME business owner for their employees.
 */
class Task
{
    private array $assignees = [];

    private function __construct(
        private TaskId $id,
        private int $managerId,
        private string $title,
        private ?string $description,
        private ?DateTimeImmutable $dueDate,
        private TaskPriority $priority,
        private TaskStatus $status,
        private ?string $category,
        private int $progressPercentage,
        private ?float $estimatedHours,
        private float $actualHours,
        private ?DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $completedAt,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $managerId,
        string $title,
        ?string $description = null,
        ?DateTimeImmutable $dueDate = null,
        ?TaskPriority $priority = null,
        ?string $category = null,
        ?float $estimatedHours = null
    ): self {
        return new self(
            id: TaskId::generate(),
            managerId: $managerId,
            title: $title,
            description: $description,
            dueDate: $dueDate,
            priority: $priority ?? TaskPriority::medium(),
            status: TaskStatus::pending(),
            category: $category,
            progressPercentage: 0,
            estimatedHours: $estimatedHours,
            actualHours: 0.0,
            startedAt: null,
            completedAt: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public static function reconstitute(
        TaskId $id,
        int $managerId,
        string $title,
        ?string $description,
        ?DateTimeImmutable $dueDate,
        TaskPriority $priority,
        TaskStatus $status,
        ?string $category,
        int $progressPercentage,
        ?float $estimatedHours,
        float $actualHours,
        ?DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            $id, $managerId, $title, $description, $dueDate, $priority, $status, $category,
            $progressPercentage, $estimatedHours, $actualHours, $startedAt, $completedAt,
            $createdAt, $updatedAt
        );
    }

    public function update(
        string $title,
        ?string $description,
        ?DateTimeImmutable $dueDate,
        TaskPriority $priority,
        ?string $category,
        ?float $estimatedHours = null
    ): void {
        $this->title = $title;
        $this->description = $description;
        $this->dueDate = $dueDate;
        $this->priority = $priority;
        $this->category = $category;
        if ($estimatedHours !== null) {
            $this->estimatedHours = $estimatedHours;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateStatus(TaskStatus $status): void
    {
        $oldStatus = $this->status;
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();

        // Auto-set started_at when moving to in_progress
        if ($status->isInProgress() && $this->startedAt === null) {
            $this->startedAt = new DateTimeImmutable();
        }

        // Auto-set completed_at and progress when completing
        if ($status->isDone()) {
            $this->completedAt = new DateTimeImmutable();
            $this->progressPercentage = 100;
        }

        // Reset completed_at if moving away from completed
        if ($oldStatus->isDone() && !$status->isDone()) {
            $this->completedAt = null;
        }
    }

    public function updateProgress(int $percentage): void
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new \InvalidArgumentException('Progress must be between 0 and 100');
        }

        $this->progressPercentage = $percentage;
        $this->updatedAt = new DateTimeImmutable();

        // Auto-transition status based on progress
        if ($percentage === 100 && !$this->status->isDone()) {
            $this->status = TaskStatus::completed();
            $this->completedAt = new DateTimeImmutable();
        } elseif ($percentage > 0 && $this->status->isPending()) {
            $this->status = TaskStatus::inProgress();
            if ($this->startedAt === null) {
                $this->startedAt = new DateTimeImmutable();
            }
        }
    }

    public function logTime(float $hours): void
    {
        if ($hours < 0) {
            throw new \InvalidArgumentException('Hours cannot be negative');
        }

        $this->actualHours += $hours;
        $this->updatedAt = new DateTimeImmutable();

        // Auto-start task if time is logged
        if ($this->status->isPending() && $hours > 0) {
            $this->status = TaskStatus::inProgress();
            if ($this->startedAt === null) {
                $this->startedAt = new DateTimeImmutable();
            }
        }
    }

    public function markAsPending(): void
    {
        $this->status = TaskStatus::pending();
        $this->progressPercentage = 0;
        $this->startedAt = null;
        $this->completedAt = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsInProgress(): void
    {
        $this->status = TaskStatus::inProgress();
        if ($this->startedAt === null) {
            $this->startedAt = new DateTimeImmutable();
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsDone(): void
    {
        $this->status = TaskStatus::done();
        $this->progressPercentage = 100;
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isOverdue(): bool
    {
        if (!$this->dueDate || $this->status->isDone()) {
            return false;
        }
        return $this->dueDate < new DateTimeImmutable('today');
    }

    public function isDueToday(): bool
    {
        if (!$this->dueDate) {
            return false;
        }
        $today = new DateTimeImmutable('today');
        return $this->dueDate->format('Y-m-d') === $today->format('Y-m-d');
    }

    public function getTimeEfficiency(): ?float
    {
        if ($this->estimatedHours === null || $this->estimatedHours <= 0 || $this->actualHours <= 0) {
            return null;
        }
        return round(($this->estimatedHours / $this->actualHours) * 100, 1);
    }

    public function getRemainingHours(): ?float
    {
        if ($this->estimatedHours === null) {
            return null;
        }
        return max(0, $this->estimatedHours - $this->actualHours);
    }

    // Getters
    public function getId(): TaskId { return $this->id; }
    public function id(): int { return $this->id->toInt(); }
    public function getManagerId(): int { return $this->managerId; }
    public function ownerId(): int { return $this->managerId; }
    public function getTitle(): string { return $this->title; }
    public function title(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function description(): ?string { return $this->description; }
    public function getDueDate(): ?DateTimeImmutable { return $this->dueDate; }
    public function dueDate(): ?DateTimeImmutable { return $this->dueDate; }
    public function getPriority(): TaskPriority { return $this->priority; }
    public function priority(): TaskPriority { return $this->priority; }
    public function getStatus(): TaskStatus { return $this->status; }
    public function status(): TaskStatus { return $this->status; }
    public function getCategory(): ?string { return $this->category; }
    public function category(): ?string { return $this->category; }
    public function getProgressPercentage(): int { return $this->progressPercentage; }
    public function progressPercentage(): int { return $this->progressPercentage; }
    public function getEstimatedHours(): ?float { return $this->estimatedHours; }
    public function estimatedHours(): ?float { return $this->estimatedHours; }
    public function getActualHours(): float { return $this->actualHours; }
    public function actualHours(): float { return $this->actualHours; }
    public function getStartedAt(): ?DateTimeImmutable { return $this->startedAt; }
    public function startedAt(): ?DateTimeImmutable { return $this->startedAt; }
    public function getCompletedAt(): ?DateTimeImmutable { return $this->completedAt; }
    public function completedAt(): ?DateTimeImmutable { return $this->completedAt; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'owner_id' => $this->managerId,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'priority' => $this->priority->value(),
            'status' => $this->status->value(),
            'category' => $this->category,
            'progress_percentage' => $this->progressPercentage,
            'estimated_hours' => $this->estimatedHours,
            'actual_hours' => $this->actualHours,
            'time_efficiency' => $this->getTimeEfficiency(),
            'remaining_hours' => $this->getRemainingHours(),
            'started_at' => $this->startedAt?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'is_overdue' => $this->isOverdue(),
            'is_due_today' => $this->isDueToday(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
