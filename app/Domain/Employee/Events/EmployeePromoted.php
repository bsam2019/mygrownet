<?php

declare(strict_types=1);

namespace App\Domain\Employee\Events;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\DepartmentId;
use App\Domain\Employee\ValueObjects\PositionId;
use App\Domain\Employee\ValueObjects\Salary;
use DateTimeImmutable;

/**
 * Domain event fired when an employee is promoted
 */
final readonly class EmployeePromoted
{
    public function __construct(
        public EmployeeId $employeeId,
        public ?DepartmentId $previousDepartmentId,
        public DepartmentId $newDepartmentId,
        public ?PositionId $previousPositionId,
        public PositionId $newPositionId,
        public ?Salary $previousSalary,
        public Salary $newSalary,
        public string $promotionReason,
        public int $promotedBy,
        public DateTimeImmutable $effectiveDate,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}

    /**
     * Get event data as array for serialization
     */
    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId->toString(),
            'previous_department_id' => $this->previousDepartmentId?->toString(),
            'new_department_id' => $this->newDepartmentId->toString(),
            'previous_position_id' => $this->previousPositionId?->toString(),
            'new_position_id' => $this->newPositionId->toString(),
            'previous_salary' => $this->previousSalary?->getAmount(),
            'new_salary' => $this->newSalary->getAmount(),
            'promotion_reason' => $this->promotionReason,
            'promoted_by' => $this->promotedBy,
            'effective_date' => $this->effectiveDate->format('Y-m-d'),
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Get event name for logging/tracking
     */
    public function getEventName(): string
    {
        return 'employee.promoted';
    }

    /**
     * Check if this is a department change
     */
    public function isDepartmentChange(): bool
    {
        return $this->previousDepartmentId !== null 
            && !$this->previousDepartmentId->equals($this->newDepartmentId);
    }

    /**
     * Check if this is a position change
     */
    public function isPositionChange(): bool
    {
        return $this->previousPositionId !== null 
            && !$this->previousPositionId->equals($this->newPositionId);
    }

    /**
     * Check if this is a salary increase
     */
    public function isSalaryIncrease(): bool
    {
        return $this->previousSalary !== null 
            && $this->newSalary->getAmount() > $this->previousSalary->getAmount();
    }

    /**
     * Validate event data integrity
     */
    public function isValid(): bool
    {
        return !empty($this->promotionReason)
            && $this->promotedBy > 0
            && $this->newSalary->getAmount() > 0
            && $this->effectiveDate <= $this->occurredAt
            && ($this->isDepartmentChange() || $this->isPositionChange() || $this->isSalaryIncrease());
    }
}