<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Exceptions;

class TaskNotFoundException extends GrowStartException
{
    protected string $errorCode = 'TASK_NOT_FOUND';

    public function __construct(string $message = 'Task not found')
    {
        parent::__construct($message);
    }
}
