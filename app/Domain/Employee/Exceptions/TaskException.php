<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

class TaskException extends EmployeeDomainException
{
    public static function invalidStatusTransition(string $from, string $to): self
    {
        return new self("Cannot transition task from '{$from}' to '{$to}'");
    }

    public static function cannotReopenClosedTask(string $taskId): self
    {
        return new self("Cannot reopen closed task: {$taskId}");
    }

    public static function invalidTitle(string $message): self
    {
        return new self("Invalid task title: {$message}");
    }

    public static function taskNotFound(string $taskId): self
    {
        return new self("Task not found: {$taskId}");
    }

    public static function unauthorizedAccess(string $taskId, string $employeeId): self
    {
        return new self("Employee {$employeeId} is not authorized to access task {$taskId}");
    }
}
