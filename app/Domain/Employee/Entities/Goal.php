<?php

declare(strict_types=1);

namespace App\Domain\Employee\Entities;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\GoalStatus;
use App\Domain\Employee\Exceptions\GoalException;
use DateTimeImmutable;

final class Goal
{
    private int $id;
    private EmployeeId $employeeId;
    private string $title;
    private ?string $description;
    private string $category;
    private int $progress;
    private GoalStatus $status;
    private DateTimeImmutable $startDate;
    private DateTimeImmutable $dueDate;
    private ?DateTimeImmutable $completedAt;
    private array $milestones;
    private ?EmployeeId $approvedBy;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct(
        EmployeeId $employeeId,
        string $title,
        string $category,
        DateTimeImmutable $startDate,
        DateTimeImmutable $dueDate,
        ?string $description = null,
        array $milestones = []
    ) {
        $this->validateTitle($title);
        $this->validateDates($startDate, $dueDate);

        $this->employeeId = $employeeId;
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->progress = 0;
        $this->status = GoalStatus::pending();
        $this->startDate = $startDate;
        $this->dueDate = $dueDate;
        $this->completedAt = null;
        $this->milestones = $milestones;
        $this->approvedBy = null;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(
        EmployeeId $employeeId,
        string $title,
        string $category,
        DateTimeImmutable $startDate,
        DateTimeImmutable $dueDate,
        ?string $description = null,
        array $milestones = []
    ): self {
        return new self(
            $employeeId,
            $title,
            $category,
            $startDate,
            $dueDate,
            $description,
            $milestones
        );
    }

    public function updateProgress(int $progress): void
    {
        $this->progress = min(100, max(0, $progress));
        
        if ($this->progress >= 100) {
            $this->status = GoalStatus::completed();
            $this->completedAt = new DateTimeImmutable();
        } elseif ($this->progress > 0 && $this->status->isPending()) {
            $this->status = GoalStatus::inProgress();
        }
        
        $this->updatedAt = new DateTimeImmutable();
    }

    public function approve(EmployeeId $approverId): void
    {
        $this->approvedBy = $approverId;
        $this->status = GoalStatus::inProgress();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function complete(): void
    {
        $this->progress = 100;
        $this->status = GoalStatus::completed();
        $this->completedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        $this->status = GoalStatus::cancelled();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateMilestone(int $index, bool $completed): void
    {
        if (!isset($this->milestones[$index])) {
            throw GoalException::milestoneNotFound($index);
        }

        $this->milestones[$index]['completed'] = $completed;
        $this->milestones[$index]['completed_at'] = $completed ? (new DateTimeImmutable())->format('Y-m-d H:i:s') : null;
        
        // Recalculate progress based on milestones
        $this->recalculateProgressFromMilestones();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addMilestone(string $title, ?DateTimeImmutable $targetDate = null): void
    {
        $this->milestones[] = [
            'title' => $title,
            'target_date' => $targetDate?->format('Y-m-d'),
            'completed' => false,
            'completed_at' => null,
        ];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDetails(
        string $title,
        ?string $description,
        string $category,
        DateTimeImmutable $dueDate
    ): void {
        $this->validateTitle($title);
        $this->validateDates($this->startDate, $dueDate);

        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->dueDate = $dueDate;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isOverdue(): bool
    {
        if ($this->status->isCompleted() || $this->status->isCancelled()) {
            return false;
        }

        return $this->dueDate < new DateTimeImmutable();
    }

    public function getDaysRemaining(): int
    {
        $now = new DateTimeImmutable();
        $interval = $now->diff($this->dueDate);
        
        return $interval->invert ? -$interval->days : $interval->days;
    }

    public function getCompletedMilestonesCount(): int
    {
        return count(array_filter($this->milestones, fn($m) => $m['completed'] ?? false));
    }

    private function recalculateProgressFromMilestones(): void
    {
        if (empty($this->milestones)) {
            return;
        }

        $completed = $this->getCompletedMilestonesCount();
        $total = count($this->milestones);
        
        $this->progress = (int) round(($completed / $total) * 100);
        
        if ($this->progress >= 100) {
            $this->status = GoalStatus::completed();
            $this->completedAt = new DateTimeImmutable();
        }
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getEmployeeId(): EmployeeId { return $this->employeeId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getCategory(): string { return $this->category; }
    public function getProgress(): int { return $this->progress; }
    public function getStatus(): GoalStatus { return $this->status; }
    public function getStartDate(): DateTimeImmutable { return $this->startDate; }
    public function getDueDate(): DateTimeImmutable { return $this->dueDate; }
    public function getCompletedAt(): ?DateTimeImmutable { return $this->completedAt; }
    public function getMilestones(): array { return $this->milestones; }
    public function getApprovedBy(): ?EmployeeId { return $this->approvedBy; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function setId(int $id): void { $this->id = $id; }

    private function validateTitle(string $title): void
    {
        if (empty(trim($title))) {
            throw GoalException::invalidTitle('Title cannot be empty');
        }
    }

    private function validateDates(DateTimeImmutable $start, DateTimeImmutable $end): void
    {
        if ($end <= $start) {
            throw GoalException::invalidDateRange();
        }
    }
}
