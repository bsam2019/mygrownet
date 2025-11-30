<?php

declare(strict_types=1);

namespace App\Domain\Employee\Entities;

use App\Domain\Employee\ValueObjects\TaskId;
use App\Domain\Employee\ValueObjects\TaskPriority;
use App\Domain\Employee\ValueObjects\TaskStatus;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\Exceptions\TaskException;
use DateTimeImmutable;

final class Task
{
    private TaskId $id;
    private string $title;
    private ?string $description;
    private EmployeeId $assignedTo;
    private ?EmployeeId $assignedBy;
    private ?int $departmentId;
    private TaskPriority $priority;
    private TaskStatus $status;
    private ?DateTimeImmutable $dueDate;
    private ?DateTimeImmutable $startedAt;
    private ?DateTimeImmutable $completedAt;
    private ?float $estimatedHours;
    private ?float $actualHours;
    private array $tags;
    private ?string $notes;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(
        TaskId $id,
        string $title,
        EmployeeId $assignedTo,
        TaskPriority $priority,
        TaskStatus $status,
        ?string $description = null,
        ?EmployeeId $assignedBy = null,
        ?int $departmentId = null,
        ?DateTimeImmutable $dueDate = null,
        ?float $estimatedHours = null,
        array $tags = [],
        ?string $notes = null
    ) {
        $this->validateTitle($title);

        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->assignedTo = $assignedTo;
        $this->assignedBy = $assignedBy;
        $this->departmentId = $departmentId;
        $this->priority = $priority;
        $this->status = $status;
        $this->dueDate = $dueDate;
        $this->startedAt = null;
        $this->completedAt = null;
        $this->estimatedHours = $estimatedHours;
        $this->actualHours = null;
        $this->tags = $tags;
        $this->notes = $notes;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(
        string $title,
        EmployeeId $assignedTo,
        TaskPriority $priority,
        ?string $description = null,
        ?EmployeeId $assignedBy = null,
        ?int $departmentId = null,
        ?DateTimeImmutable $dueDate = null,
        ?float $estimatedHours = null,
        array $tags = []
    ): self {
        return new self(
            TaskId::generate(),
            $title,
            $assignedTo,
            $priority,
            TaskStatus::todo(),
            $description,
            $assignedBy,
            $departmentId,
            $dueDate,
            $estimatedHours,
            $tags
        );
    }

    public function start(): void
    {
        if (!$this->status->canTransitionTo('in_progress')) {
            throw TaskException::invalidStatusTransition($this->status->getValue(), 'in_progress');
        }

        $this->status = TaskStatus::inProgress();
        $this->startedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function submitForReview(): void
    {
        if (!$this->status->canTransitionTo('review')) {
            throw TaskException::invalidStatusTransition($this->status->getValue(), 'review');
        }

        $this->status = TaskStatus::review();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(?float $actualHours = null): void
    {
        if (!$this->status->canTransitionTo('completed')) {
            throw TaskException::invalidStatusTransition($this->status->getValue(), 'completed');
        }

        $this->status = TaskStatus::completed();
        $this->completedAt = new DateTimeImmutable();
        $this->actualHours = $actualHours;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if (!$this->status->canTransitionTo('cancelled')) {
            throw TaskException::invalidStatusTransition($this->status->getValue(), 'cancelled');
        }

        $this->status = TaskStatus::cancelled();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reopen(): void
    {
        if ($this->status->isCompleted() || $this->status->isCancelled()) {
            throw TaskException::cannotReopenClosedTask($this->id->toString());
        }

        $this->status = TaskStatus::todo();
        $this->startedAt = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDetails(
        string $title,
        ?string $description = null,
        ?DateTimeImmutable $dueDate = null,
        ?float $estimatedHours = null,
        array $tags = []
    ): void {
        $this->validateTitle($title);

        $this->title = $title;
        $this->description = $description;
        $this->dueDate = $dueDate;
        $this->estimatedHours = $estimatedHours;
        $this->tags = $tags;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changePriority(TaskPriority $priority): void
    {
        $this->priority = $priority;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reassign(EmployeeId $newAssignee): void
    {
        $this->assignedTo = $newAssignee;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addNotes(string $notes): void
    {
        $timestamp = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $newNote = "[{$timestamp}] {$notes}";
        
        $this->notes = empty($this->notes) 
            ? $newNote 
            : $this->notes . "\n" . $newNote;
            
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isOverdue(): bool
    {
        if (!$this->dueDate || $this->status->isClosed()) {
            return false;
        }

        return $this->dueDate < new DateTimeImmutable();
    }

    public function getDaysUntilDue(): ?int
    {
        if (!$this->dueDate) {
            return null;
        }

        $now = new DateTimeImmutable();
        $interval = $now->diff($this->dueDate);
        
        return $interval->invert ? -$interval->days : $interval->days;
    }

    // Getters
    public function getId(): TaskId { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getAssignedTo(): EmployeeId { return $this->assignedTo; }
    public function getAssignedBy(): ?EmployeeId { return $this->assignedBy; }
    public function getDepartmentId(): ?int { return $this->departmentId; }
    public function getPriority(): TaskPriority { return $this->priority; }
    public function getStatus(): TaskStatus { return $this->status; }
    public function getDueDate(): ?DateTimeImmutable { return $this->dueDate; }
    public function getStartedAt(): ?DateTimeImmutable { return $this->startedAt; }
    public function getCompletedAt(): ?DateTimeImmutable { return $this->completedAt; }
    public function getEstimatedHours(): ?float { return $this->estimatedHours; }
    public function getActualHours(): ?float { return $this->actualHours; }
    public function getTags(): array { return $this->tags; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    private function validateTitle(string $title): void
    {
        $trimmed = trim($title);
        if (empty($trimmed)) {
            throw TaskException::invalidTitle('Title cannot be empty');
        }
        if (strlen($trimmed) > 255) {
            throw TaskException::invalidTitle('Title cannot exceed 255 characters');
        }
    }
}
