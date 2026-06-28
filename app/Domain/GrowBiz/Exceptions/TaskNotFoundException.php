<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Exceptions;

class TaskNotFoundException extends GrowBizException
{
    public function __construct(int $taskId)
    {
        parent::__construct(
            message: "Task with ID {$taskId} not found.",
            errorCode: 'TASK_NOT_FOUND',
            context: ['task_id' => $taskId],
            code: 404
        );
    }
}
