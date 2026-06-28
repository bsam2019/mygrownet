<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

final class DepartmentException extends EmployeeDomainException
{
    public static function circularReference(string $departmentName): self
    {
        return new self("Circular reference detected in department hierarchy for: {$departmentName}");
    }

    public static function employeeNotInDepartment(string $employeeId, string $departmentName): self
    {
        return new self("Employee {$employeeId} is not in department {$departmentName}");
    }

    public static function employeeAlreadyInDepartment(string $employeeId, string $departmentName): self
    {
        return new self("Employee {$employeeId} is already in department {$departmentName}");
    }

    public static function cannotAssignHeadFromOutsideDepartment(string $employeeId, string $departmentName): self
    {
        return new self("Cannot assign employee {$employeeId} as head of department {$departmentName} - employee must be in the department");
    }

    public static function invalidName(string $name): self
    {
        return new self("Invalid department name: {$name}");
    }
}