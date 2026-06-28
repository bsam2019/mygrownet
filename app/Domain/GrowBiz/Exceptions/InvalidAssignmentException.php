<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Exceptions;

class InvalidAssignmentException extends GrowBizException
{
    public function __construct(int $taskId, array $invalidEmployeeIds)
    {
        $ids = implode(', ', $invalidEmployeeIds);
        parent::__construct(
            message: "Cannot assign task to invalid or inactive employees: {$ids}.",
            errorCode: 'INVALID_ASSIGNMENT',
            context: [
                'task_id' => $taskId,
                'invalid_employee_ids' => $invalidEmployeeIds,
            ],
            code: 422
        );
    }
}
