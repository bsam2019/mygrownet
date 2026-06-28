<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

final class EmployeeException extends EmployeeDomainException
{
    public static function invalidName(string $name): self
    {
        return new self("Invalid employee name: {$name}");
    }

    public static function invalidEmploymentStatusTransition(string $from, string $to): self
    {
        return new self("Invalid employment status transition from {$from} to {$to}");
    }

    public static function cannotAssignManagerFromDifferentDepartment(string $employeeId, string $managerId): self
    {
        return new self("Cannot assign manager {$managerId} to employee {$employeeId} - manager must be in the same department or a parent department");
    }

    public static function cannotAssignSelfAsManager(string $employeeId): self
    {
        return new self("Employee {$employeeId} cannot be assigned as their own manager");
    }

    public static function userAlreadyAssigned(string $userId): self
    {
        return new self("User {$userId} is already assigned to another employee");
    }

    public static function invalidHireDate(): self
    {
        return new self("Hire date cannot be in the future");
    }

    public static function invalidCommissionRate(float $rate): self
    {
        return new self("Invalid commission rate: {$rate}. Rate must be between 0 and 100");
    }

    public static function salaryOutOfRange(float $salary, float $min, float $max): self
    {
        return new self("Salary {$salary} is out of range for position (min: {$min}, max: {$max})");
    }

    public static function userAccountAlreadyExists(string $email): self
    {
        return new self("User account with email {$email} already exists");
    }

    public static function noUserAccountAssigned(string $employeeId): self
    {
        return new self("No user account assigned to employee {$employeeId}");
    }

    public static function inactiveEmployeePayroll(string $employeeId): self
    {
        return new self("Cannot process payroll for inactive employee {$employeeId}");
    }

    public static function invalidPayrollPeriod(string $startDate, string $endDate): self
    {
        return new self("Invalid payroll period: start date {$startDate} must be before end date {$endDate}");
    }
}