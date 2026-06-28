<?php

declare(strict_types=1);

namespace App\Domain\Employee\Exceptions;

class GoalException extends EmployeeDomainException
{
    public static function invalidTitle(string $message): self
    {
        return new self("Invalid goal title: {$message}");
    }

    public static function invalidDateRange(): self
    {
        return new self('Due date must be after start date');
    }

    public static function milestoneNotFound(int $index): self
    {
        return new self("Milestone not found at index: {$index}");
    }

    public static function goalNotFound(int $goalId): self
    {
        return new self("Goal not found: {$goalId}");
    }

    public static function cannotModifyCompletedGoal(int $goalId): self
    {
        return new self("Cannot modify completed goal: {$goalId}");
    }
}
