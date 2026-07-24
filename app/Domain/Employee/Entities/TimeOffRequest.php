<?php

declare(strict_types=1);

namespace App\Domain\Employee\Entities;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\TimeOffType;
use App\Domain\Employee\Exceptions\TimeOffException;
use DateTimeImmutable;

final class TimeOffRequest
{
    private int $id;
    private EmployeeId $employeeId;
    private TimeOffType $type;
    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;
    private float $daysRequested;
    private ?string $reason;
    private string $status;
    private ?EmployeeId $reviewedBy;
    private ?DateTimeImmutable $reviewedAt;
    private ?string $reviewNotes;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private const STATUS_PENDING = 'pending';
    private const STATUS_APPROVED = 'approved';
    private const STATUS_REJECTED = 'rejected';
    private const STATUS_CANCELLED = 'cancelled';

    private function __construct(
        EmployeeId $employeeId,
        TimeOffType $type,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        float $daysRequested,
        ?string $reason = null
    ) {
        $this->validateDates($startDate, $endDate);
        $this->validateDaysRequested($daysRequested, $startDate, $endDate);

        $this->employeeId = $employeeId;
        $this->type = $type;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->daysRequested = $daysRequested;
        $this->reason = $reason;
        $this->status = self::STATUS_PENDING;
        $this->reviewedBy = null;
        $this->reviewedAt = null;
        $this->reviewNotes = null;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(
        EmployeeId $employeeId,
        TimeOffType $type,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        float $daysRequested,
        ?string $reason = null
    ): self {
        return new self(
            $employeeId,
            $type,
            $startDate,
            $endDate,
            $daysRequested,
            $reason
        );
    }

    public static function reconstitute(array $data): self
    {
        $instance = new self(
            $data['employee_id'],
            $data['type'],
            $data['start_date'],
            $data['end_date'],
            $data['days_requested'],
            $data['reason'] ?? null
        );
        $instance->id = $data['id'] ?? 0;
        $instance->status = $data['status'] ?? self::STATUS_PENDING;
        $instance->reviewedBy = $data['reviewed_by'] ?? null;
        $instance->reviewedAt = $data['reviewed_at'] ?? null;
        $instance->reviewNotes = $data['review_notes'] ?? null;
        $instance->createdAt = $data['created_at'] ?? new DateTimeImmutable();
        $instance->updatedAt = $data['updated_at'] ?? new DateTimeImmutable();
        return $instance;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employeeId,
            'type' => $this->type,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'days_requested' => $this->daysRequested,
            'reason' => $this->reason,
            'status' => $this->status,
            'reviewed_by' => $this->reviewedBy,
            'reviewed_at' => $this->reviewedAt,
            'review_notes' => $this->reviewNotes,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function approve(EmployeeId $reviewerId, ?string $notes = null): void
    {
        if (!$this->isPending()) {
            throw TimeOffException::cannotApproveNonPendingRequest($this->id);
        }

        $this->status = self::STATUS_APPROVED;
        $this->reviewedBy = $reviewerId;
        $this->reviewedAt = new DateTimeImmutable();
        $this->reviewNotes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reject(EmployeeId $reviewerId, ?string $notes = null): void
    {
        if (!$this->isPending()) {
            throw TimeOffException::cannotRejectNonPendingRequest($this->id);
        }

        $this->status = self::STATUS_REJECTED;
        $this->reviewedBy = $reviewerId;
        $this->reviewedAt = new DateTimeImmutable();
        $this->reviewNotes = $notes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        if (!$this->isPending()) {
            throw TimeOffException::cannotCancelNonPendingRequest($this->id);
        }

        $this->status = self::STATUS_CANCELLED;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isUpcoming(): bool
    {
        return $this->isApproved() && $this->startDate > new DateTimeImmutable();
    }

    public function isOngoing(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        $now = new DateTimeImmutable();
        return $now >= $this->startDate && $now <= $this->endDate;
    }

    public function getDurationInDays(): int
    {
        return $this->startDate->diff($this->endDate)->days + 1;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getEmployeeId(): EmployeeId { return $this->employeeId; }
    public function getType(): TimeOffType { return $this->type; }
    public function getStartDate(): DateTimeImmutable { return $this->startDate; }
    public function getEndDate(): DateTimeImmutable { return $this->endDate; }
    public function getDaysRequested(): float { return $this->daysRequested; }
    public function getReason(): ?string { return $this->reason; }
    public function getStatus(): string { return $this->status; }
    public function getReviewedBy(): ?EmployeeId { return $this->reviewedBy; }
    public function getReviewedAt(): ?DateTimeImmutable { return $this->reviewedAt; }
    public function getReviewNotes(): ?string { return $this->reviewNotes; }
    public function getCreatedAt(): DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function setId(int $id): void { $this->id = $id; }

    private function validateDates(DateTimeImmutable $start, DateTimeImmutable $end): void
    {
        if ($end < $start) {
            throw TimeOffException::invalidDateRange();
        }

        $today = new DateTimeImmutable('today');
        if ($start < $today) {
            throw TimeOffException::cannotRequestPastDates();
        }
    }

    private function validateDaysRequested(float $days, DateTimeImmutable $start, DateTimeImmutable $end): void
    {
        if ($days <= 0) {
            throw TimeOffException::invalidDaysRequested();
        }

        $maxDays = $start->diff($end)->days + 1;
        if ($days > $maxDays) {
            throw TimeOffException::daysExceedRange($days, $maxDays);
        }
    }
}
