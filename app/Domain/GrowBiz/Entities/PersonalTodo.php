<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Entities;

use App\Domain\GrowBiz\ValueObjects\TodoId;
use App\Domain\GrowBiz\ValueObjects\TodoStatus;
use App\Domain\GrowBiz\ValueObjects\TodoPriority;
use DateTimeImmutable;

/**
 * Personal To-Do Entity
 * 
 * Represents a personal task for individual user productivity.
 */
class PersonalTodo
{
    private array $subtasks = [];

    private function __construct(
        private TodoId $id,
        private int $userId,
        private string $title,
        private ?string $description,
        private ?DateTimeImmutable $dueDate,
        private ?string $dueTime,
        private TodoPriority $priority,
        private TodoStatus $status,
        private ?string $category,
        private array $tags,
        private bool $isRecurring,
        private ?string $recurrencePattern,
        private ?int $parentId,
        private int $sortOrder,
        private ?DateTimeImmutable $completedAt,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function create(
        int $userId,
        string $title,
        ?string $description = null,
        ?DateTimeImmutable $dueDate = null,
        ?string $dueTime = null,
        ?TodoPriority $priority = null,
        ?string $category = null,
        array $tags = [],
        bool $isRecurring = false,
        ?string $recurrencePattern = null,
        ?int $parentId = null
    ): self {
        return new self(
            id: TodoId::generate(),
            userId: $userId,
            title: $title,
            description: $description,
            dueDate: $dueDate,
            dueTime: $dueTime,
            priority: $priority ?? TodoPriority::medium(),
            status: TodoStatus::pending(),
            category: $category,
            tags: $tags,
            isRecurring: $isRecurring,
            recurrencePattern: $recurrencePattern,
            parentId: $parentId,
            sortOrder: 0,
            completedAt: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public static function reconstitute(
        TodoId $id,
        int $userId,
        string $title,
        ?string $description,
        ?DateTimeImmutable $dueDate,
        ?string $dueTime,
        TodoPriority $priority,
        TodoStatus $status,
        ?string $category,
        array $tags,
        bool $isRecurring,
        ?string $recurrencePattern,
        ?int $parentId,
        int $sortOrder,
        ?DateTimeImmutable $completedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            $id, $userId, $title, $description, $dueDate, $dueTime, $priority, $status,
            $category, $tags, $isRecurring, $recurrencePattern, $parentId, $sortOrder,
            $completedAt, $createdAt, $updatedAt
        );
    }

    public function update(
        string $title,
        ?string $description,
        ?DateTimeImmutable $dueDate,
        ?string $dueTime,
        TodoPriority $priority,
        ?string $category,
        array $tags = []
    ): void {
        $this->title = $title;
        $this->description = $description;
        $this->dueDate = $dueDate;
        $this->dueTime = $dueTime;
        $this->priority = $priority;
        $this->category = $category;
        $this->tags = $tags;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateStatus(TodoStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();

        if ($status->isCompleted()) {
            $this->completedAt = new DateTimeImmutable();
        } else {
            $this->completedAt = null;
        }
    }

    public function markAsCompleted(): void
    {
        $this->status = TodoStatus::completed();
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsPending(): void
    {
        $this->status = TodoStatus::pending();
        $this->completedAt = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsInProgress(): void
    {
        $this->status = TodoStatus::inProgress();
        $this->completedAt = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isOverdue(): bool
    {
        if (!$this->dueDate || $this->status->isCompleted()) {
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

    public function isDueTomorrow(): bool
    {
        if (!$this->dueDate) {
            return false;
        }
        $tomorrow = new DateTimeImmutable('tomorrow');
        return $this->dueDate->format('Y-m-d') === $tomorrow->format('Y-m-d');
    }

    public function isSubtask(): bool
    {
        return $this->parentId !== null;
    }

    public function setSortOrder(int $order): void
    {
        $this->sortOrder = $order;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function getId(): TodoId { return $this->id; }
    public function id(): int { return $this->id->toInt(); }
    public function getUserId(): int { return $this->userId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getDueDate(): ?DateTimeImmutable { return $this->dueDate; }
    public function getDueTime(): ?string { return $this->dueTime; }
    public function getPriority(): TodoPriority { return $this->priority; }
    public function getStatus(): TodoStatus { return $this->status; }
    public function getCategory(): ?string { return $this->category; }
    public function getTags(): array { return $this->tags; }
    public function isRecurring(): bool { return $this->isRecurring; }
    public function getRecurrencePattern(): ?string { return $this->recurrencePattern; }
    public function getParentId(): ?int { return $this->parentId; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function getCompletedAt(): ?DateTimeImmutable { return $this->completedAt; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'user_id' => $this->userId,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->dueDate?->format('Y-m-d'),
            'due_time' => $this->dueTime,
            'priority' => $this->priority->value(),
            'status' => $this->status->value(),
            'category' => $this->category,
            'tags' => $this->tags,
            'is_recurring' => $this->isRecurring,
            'recurrence_pattern' => $this->recurrencePattern,
            'parent_id' => $this->parentId,
            'sort_order' => $this->sortOrder,
            'is_overdue' => $this->isOverdue(),
            'is_due_today' => $this->isDueToday(),
            'is_due_tomorrow' => $this->isDueTomorrow(),
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
