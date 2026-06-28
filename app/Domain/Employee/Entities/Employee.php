<?php

declare(strict_types=1);

namespace App\Domain\Employee\Entities;

use App\Domain\Employee\Exceptions\EmployeeException;
use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Domain\Employee\ValueObjects\EmploymentStatus;
use App\Domain\Employee\ValueObjects\Email;
use App\Domain\Employee\ValueObjects\Phone;
use App\Domain\Employee\ValueObjects\Salary;
use App\Domain\Employee\ValueObjects\PerformanceMetrics;
use DateTimeImmutable;

final class Employee
{
    private EmployeeId $id;
    private string $employeeNumber;
    private string $firstName;
    private string $lastName;
    private Email $email;
    private ?Phone $phone;
    private ?string $address;
    private DateTimeImmutable $hireDate;
    private ?DateTimeImmutable $terminationDate;
    private EmploymentStatus $status;
    private Department $department;
    private Position $position;
    private ?Employee $manager;
    private ?int $userId; // Reference by ID instead of object
    private Salary $baseSalary;
    private float $commissionRate;
    private ?PerformanceMetrics $lastPerformanceMetrics;
    private ?DateTimeImmutable $lastPerformanceReview;
    private ?string $notes;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        EmployeeId $id,
        string $employeeNumber,
        string $firstName,
        string $lastName,
        Email $email,
        DateTimeImmutable $hireDate,
        EmploymentStatus $status,
        Department $department,
        Position $position,
        Salary $baseSalary,
        ?Phone $phone = null,
        ?string $address = null,
        ?Employee $manager = null,
        ?User $user = null,
        float $commissionRate = 0.0,
        ?string $notes = null
    ) {
        $this->validateNames($firstName, $lastName);
        $this->validateHireDate($hireDate);
        $this->validateManager($manager, $department);
        $this->validateSalaryForPosition($baseSalary, $position);

        $this->id = $id;
        $this->employeeNumber = $employeeNumber;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->hireDate = $hireDate;
        $this->terminationDate = null;
        $this->status = $status;
        $this->department = $department;
        $this->position = $position;
        $this->manager = $manager;
        $this->user = $user;
        $this->baseSalary = $baseSalary;
        $this->commissionRate = $commissionRate;
        $this->lastPerformanceMetrics = null;
        $this->lastPerformanceReview = null;
        $this->notes = $notes;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(
        string $employeeNumber,
        string $firstName,
        string $lastName,
        Email $email,
        DateTimeImmutable $hireDate,
        Department $department,
        Position $position,
        Salary $baseSalary,
        ?Phone $phone = null,
        ?string $address = null,
        ?Employee $manager = null
    ): self {
        return new self(
            EmployeeId::generate(),
            $employeeNumber,
            $firstName,
            $lastName,
            $email,
            $hireDate,
            EmploymentStatus::active(),
            $department,
            $position,
            $baseSalary,
            $phone,
            $address,
            $manager
        );
    }

    public function assignToUser(int $userId): void
    {
        $this->userId = $userId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function unassignFromUser(): void
    {
        $this->userId = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePerformance(PerformanceMetrics $metrics, ?DateTimeImmutable $reviewDate = null): void
    {
        $this->lastPerformanceMetrics = $metrics;
        $this->lastPerformanceReview = $reviewDate ?? new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function calculateTotalCompensation(?DateTimeImmutable $periodStart = null, ?DateTimeImmutable $periodEnd = null): Salary
    {
        $totalCompensation = $this->baseSalary;

        // Add commission if eligible and performance metrics are available
        if ($this->position->isCommissionEligible() && $this->lastPerformanceMetrics !== null) {
            $commissionBase = Salary::fromKwacha($this->lastPerformanceMetrics->getCommissionGenerated());
            $commission = $this->position->calculateCommission($commissionBase);
            $totalCompensation = $totalCompensation->add($commission);
        }

        return $totalCompensation;
    }

    public function changeEmploymentStatus(EmploymentStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus->getStatus())) {
            throw EmployeeException::invalidEmploymentStatusTransition(
                $this->status->getStatus(),
                $newStatus->getStatus()
            );
        }

        $this->status = $newStatus;
        
        // Set termination date if status is terminated
        if ($newStatus->isTerminated()) {
            $this->terminationDate = $newStatus->getEffectiveDate() ?? new DateTimeImmutable();
        }

        $this->updatedAt = new DateTimeImmutable();
    }

    public function assignManager(Employee $manager): void
    {
        if ($this->id->equals($manager->getId())) {
            throw EmployeeException::cannotAssignSelfAsManager($this->id->toString());
        }

        $this->validateManager($manager, $this->department);
        $this->manager = $manager;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function removeManager(): void
    {
        $this->manager = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function transferToDepartment(Department $newDepartment, Position $newPosition): void
    {
        $this->validateSalaryForPosition($this->baseSalary, $newPosition);
        
        $this->department = $newDepartment;
        $this->position = $newPosition;
        
        // Clear manager if they're not in the new department hierarchy
        if ($this->manager && !$this->isManagerValidForDepartment($this->manager, $newDepartment)) {
            $this->manager = null;
        }
        
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePersonalDetails(
        string $firstName,
        string $lastName,
        Email $email,
        ?Phone $phone = null,
        ?string $address = null
    ): void {
        $this->validateNames($firstName, $lastName);
        
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateSalary(Salary $newSalary): void
    {
        $this->validateSalaryForPosition($newSalary, $this->position);
        
        $this->baseSalary = $newSalary;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateCommissionRate(float $commissionRate): void
    {
        if ($commissionRate < 0 || $commissionRate > 100) {
            throw EmployeeException::invalidCommissionRate($commissionRate);
        }
        
        $this->commissionRate = $commissionRate;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addNotes(string $notes): void
    {
        $existingNotes = $this->notes ?? '';
        $timestamp = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $newNote = "[{$timestamp}] {$notes}";
        
        $this->notes = empty($existingNotes) 
            ? $newNote 
            : $existingNotes . "\n" . $newNote;
            
        $this->updatedAt = new DateTimeImmutable();
    }

    // Query methods
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isTerminated(): bool
    {
        return $this->status->isTerminated();
    }

    public function hasManager(): bool
    {
        return $this->manager !== null;
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function hasPerformanceMetrics(): bool
    {
        return $this->lastPerformanceMetrics !== null;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getYearsOfService(?DateTimeImmutable $asOf = null): int
    {
        $endDate = $asOf ?? new DateTimeImmutable();
        $interval = $this->hireDate->diff($endDate);
        return $interval->y;
    }

    public function isEligibleForCommission(): bool
    {
        return $this->position->isCommissionEligible() && $this->isActive();
    }

    // Getters
    public function getId(): EmployeeId
    {
        return $this->id;
    }

    public function getEmployeeNumber(): string
    {
        return $this->employeeNumber;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getHireDate(): DateTimeImmutable
    {
        return $this->hireDate;
    }

    public function getTerminationDate(): ?DateTimeImmutable
    {
        return $this->terminationDate;
    }

    public function getStatus(): EmploymentStatus
    {
        return $this->status;
    }

    public function getDepartment(): Department
    {
        return $this->department;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getManager(): ?Employee
    {
        return $this->manager;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getBaseSalary(): Salary
    {
        return $this->baseSalary;
    }

    public function getCommissionRate(): float
    {
        return $this->commissionRate;
    }

    public function getLastPerformanceMetrics(): ?PerformanceMetrics
    {
        return $this->lastPerformanceMetrics;
    }

    public function getLastPerformanceReview(): ?DateTimeImmutable
    {
        return $this->lastPerformanceReview;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Private validation methods
    private function validateNames(string $firstName, string $lastName): void
    {
        $trimmedFirstName = trim($firstName);
        $trimmedLastName = trim($lastName);
        
        if (empty($trimmedFirstName) || empty($trimmedLastName)) {
            throw EmployeeException::invalidName("First name and last name cannot be empty");
        }

        if (strlen($trimmedFirstName) > 100 || strlen($trimmedLastName) > 100) {
            throw EmployeeException::invalidName("Names cannot exceed 100 characters");
        }
    }

    private function validateHireDate(DateTimeImmutable $hireDate): void
    {
        $now = new DateTimeImmutable();
        if ($hireDate > $now) {
            throw EmployeeException::invalidHireDate();
        }
    }

    private function validateManager(?Employee $manager, Department $department): void
    {
        if ($manager === null) {
            return;
        }

        if (!$this->isManagerValidForDepartment($manager, $department)) {
            throw EmployeeException::cannotAssignManagerFromDifferentDepartment(
                $this->id->toString(),
                $manager->getId()->toString()
            );
        }
    }

    private function isManagerValidForDepartment(Employee $manager, Department $department): bool
    {
        // Manager can be in the same department or any parent department
        if ($manager->getDepartment()->getId()->equals($department->getId())) {
            return true;
        }

        // Check if manager is in a parent department
        $ancestors = $department->getAllAncestors();
        foreach ($ancestors as $ancestor) {
            if ($manager->getDepartment()->getId()->equals($ancestor->getId())) {
                return true;
            }
        }

        return false;
    }

    private function validateSalaryForPosition(Salary $salary, Position $position): void
    {
        if (!$position->isSalaryInRange($salary)) {
            throw EmployeeException::salaryOutOfRange(
                $salary->getAmountInKwacha(),
                $position->getBaseSalaryMin()->getAmountInKwacha(),
                $position->getBaseSalaryMax()->getAmountInKwacha()
            );
        }
    }
}