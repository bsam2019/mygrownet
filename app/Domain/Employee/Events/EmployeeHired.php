<?php

declare(strict_types=1);

namespace App\Domain\Employee\Events;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\DepartmentId;
use App\Domain\Employee\ValueObjects\PositionId;
use App\Domain\Employee\ValueObjects\Salary;
use DateTimeImmutable;

/**
 * Domain event fired when an employee is hired
 */
final readonly class EmployeeHired
{
    public function __construct(
        public EmployeeId $employeeId,
        public string $firstName,
        public string $lastName,
        public string $email,
        public DepartmentId $departmentId,
        public PositionId $positionId,
        public Salary $baseSalary,
        public DateTimeImmutable $hireDate,
        public ?int $userId = null,
        public DateTimeImmutable $occurredAt = new DateTimeImmutable()
    ) {}

    /**
     * Get event data as array for serialization
     */
    public function toArray(): array
    {
        return [
            'employee_id' => $this->employeeId->toString(),
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'department_id' => $this->departmentId->toString(),
            'position_id' => $this->positionId->toString(),
            'base_salary' => $this->baseSalary->getAmount(),
            'hire_date' => $this->hireDate->format('Y-m-d'),
            'user_id' => $this->userId,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Get event name for logging/tracking
     */
    public function getEventName(): string
    {
        return 'employee.hired';
    }

    /**
     * Validate event data integrity
     */
    public function isValid(): bool
    {
        return !empty($this->firstName) 
            && !empty($this->lastName) 
            && !empty($this->email)
            && filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false
            && $this->baseSalary->getAmount() > 0
            && $this->hireDate <= $this->occurredAt;
    }
}