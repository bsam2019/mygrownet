<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Exceptions;

class EmployeeHasActiveTasksException extends GrowBizException
{
    public function __construct(int $employeeId, int $taskCount)
    {
        parent::__construct(
            message: "Cannot delete employee with {$taskCount} active task(s). Please reassign or complete tasks first.",
            errorCode: 'EMPLOYEE_HAS_ACTIVE_TASKS',
            context: [
                'employee_id' => $employeeId,
                'active_task_count' => $taskCount,
            ],
            code: 422
        );
    }
}
