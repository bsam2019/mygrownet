<?php

namespace App\Domain\Employee\Exceptions;

/**
 * Exception thrown when an employee lacks required permissions for an operation
 */
class InsufficientPermissionsException extends EmployeeDomainException
{
    public static function forOperation(int $employeeId, string $operation): self
    {
        return new self(
            "Employee {$employeeId} lacks permissions for operation: {$operation}",
            [
                'employee_id' => $employeeId,
                'operation' => $operation
            ]
        );
    }

    public static function forResource(int $employeeId, string $resource, string $action): self
    {
        return new self(
            "Employee {$employeeId} lacks permissions to {$action} {$resource}",
            [
                'employee_id' => $employeeId,
                'resource' => $resource,
                'action' => $action
            ]
        );
    }

    public static function departmentAccess(int $employeeId, int $departmentId): self
    {
        return new self(
            "Employee {$employeeId} lacks access to department {$departmentId}",
            [
                'employee_id' => $employeeId,
                'department_id' => $departmentId
            ]
        );
    }
}