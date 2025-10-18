<?php

declare(strict_types=1);

namespace App\Domain\Employee\Entities;

use App\Domain\Employee\Exceptions\DepartmentException;
use App\Domain\Employee\ValueObjects\DepartmentId;
use DateTimeImmutable;

final class Department
{
    private DepartmentId $id;
    private string $name;
    private string $description;
    private ?Employee $head;
    private ?Department $parentDepartment;
    private array $employees;
    private array $positions;
    private array $subDepartments;
    private bool $isActive;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        DepartmentId $id,
        string $name,
        string $description,
        ?Department $parentDepartment = null,
        bool $isActive = true
    ) {
        $this->validateName($name);

        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->parentDepartment = $parentDepartment;
        $this->head = null;
        $this->employees = [];
        $this->positions = [];
        $this->subDepartments = [];
        $this->isActive = $isActive;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(
        string $name,
        string $description,
        ?Department $parentDepartment = null
    ): self {
        return new self(
            DepartmentId::generate(),
            $name,
            $description,
            $parentDepartment
        );
    }

    public function addEmployee(Employee $employee): void
    {
        if ($this->hasEmployee($employee)) {
            throw DepartmentException::employeeAlreadyInDepartment(
                $employee->getId()->toString(),
                $this->name
            );
        }

        $this->employees[] = $employee;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function removeEmployee(Employee $employee): void
    {
        if (!$this->hasEmployee($employee)) {
            throw DepartmentException::employeeNotInDepartment(
                $employee->getId()->toString(),
                $this->name
            );
        }

        // If the employee being removed is the head, clear the head position
        if ($this->head && $this->head->getId()->equals($employee->getId())) {
            $this->head = null;
        }

        $this->employees = array_filter($this->employees, function (Employee $emp) use ($employee) {
            return !$emp->getId()->equals($employee->getId());
        });
        $this->updatedAt = new DateTimeImmutable();
    }

    public function assignHead(Employee $employee): void
    {
        if (!$this->hasEmployee($employee)) {
            throw DepartmentException::cannotAssignHeadFromOutsideDepartment(
                $employee->getId()->toString(),
                $this->name
            );
        }

        $this->head = $employee;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function clearHead(): void
    {
        $this->head = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addPosition(Position $position): void
    {
        if (!$this->hasPosition($position)) {
            $this->positions[] = $position;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function removePosition(Position $position): void
    {
        $this->positions = array_filter($this->positions, function (Position $pos) use ($position) {
            return !$pos->getId()->equals($position->getId());
        });
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addSubDepartment(Department $subDepartment): void
    {
        // Prevent circular references
        if ($this->wouldCreateCircularReference($subDepartment)) {
            throw DepartmentException::circularReference($subDepartment->getName());
        }

        if (!$this->hasSubDepartment($subDepartment)) {
            $this->subDepartments[] = $subDepartment;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function removeSubDepartment(Department $subDepartment): void
    {
        $this->subDepartments = array_filter($this->subDepartments, function (Department $dept) use ($subDepartment) {
            return !$dept->getId()->equals($subDepartment->getId());
        });
        $this->updatedAt = new DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDetails(string $name, string $description): void
    {
        $this->validateName($name);
        
        $this->name = $name;
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Query methods
    public function hasEmployee(Employee $employee): bool
    {
        foreach ($this->employees as $emp) {
            if ($emp->getId()->equals($employee->getId())) {
                return true;
            }
        }
        return false;
    }

    public function hasPosition(Position $position): bool
    {
        foreach ($this->positions as $pos) {
            if ($pos->getId()->equals($position->getId())) {
                return true;
            }
        }
        return false;
    }

    public function hasSubDepartment(Department $department): bool
    {
        foreach ($this->subDepartments as $dept) {
            if ($dept->getId()->equals($department->getId())) {
                return true;
            }
        }
        return false;
    }

    public function getEmployeeCount(): int
    {
        return count($this->employees);
    }

    public function getActiveEmployeeCount(): int
    {
        return count(array_filter($this->employees, function (Employee $employee) {
            return $employee->isActive();
        }));
    }

    public function hasSubDepartments(): bool
    {
        return count($this->subDepartments) > 0;
    }

    public function isRootDepartment(): bool
    {
        return $this->parentDepartment === null;
    }

    public function getDepth(): int
    {
        $depth = 0;
        $current = $this->parentDepartment;
        
        while ($current !== null) {
            $depth++;
            $current = $current->getParentDepartment();
        }
        
        return $depth;
    }

    public function getAllAncestors(): array
    {
        $ancestors = [];
        $current = $this->parentDepartment;
        
        while ($current !== null) {
            $ancestors[] = $current;
            $current = $current->getParentDepartment();
        }
        
        return $ancestors;
    }

    public function getAllDescendants(): array
    {
        $descendants = [];
        
        foreach ($this->subDepartments as $subDepartment) {
            $descendants[] = $subDepartment;
            $descendants = array_merge($descendants, $subDepartment->getAllDescendants());
        }
        
        return $descendants;
    }

    // Getters
    public function getId(): DepartmentId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getHead(): ?Employee
    {
        return $this->head;
    }

    public function getParentDepartment(): ?Department
    {
        return $this->parentDepartment;
    }

    public function getEmployees(): array
    {
        return $this->employees;
    }

    public function getPositions(): array
    {
        return $this->positions;
    }

    public function getSubDepartments(): array
    {
        return $this->subDepartments;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Private helper methods
    private function validateName(string $name): void
    {
        $trimmedName = trim($name);
        if (empty($trimmedName)) {
            throw DepartmentException::invalidName($name);
        }

        if (strlen($trimmedName) > 100) {
            throw DepartmentException::invalidName('Department name cannot exceed 100 characters');
        }
    }



    private function wouldCreateCircularReference(Department $potentialParentOrChild): bool
    {
        // Check if the potential parent/child is actually this department
        if (isset($this->id) && $this->id->equals($potentialParentOrChild->getId())) {
            return true;
        }

        // Check if the potential parent/child has this department as an ancestor
        $ancestors = $potentialParentOrChild->getAllAncestors();
        foreach ($ancestors as $ancestor) {
            if (isset($this->id) && $this->id->equals($ancestor->getId())) {
                return true;
            }
        }

        // Check if this department has the potential parent/child as an ancestor
        // This prevents making an ancestor into a child
        $myAncestors = $this->getAllAncestors();
        foreach ($myAncestors as $ancestor) {
            if ($ancestor->getId()->equals($potentialParentOrChild->getId())) {
                return true;
            }
        }

        return false;
    }
}