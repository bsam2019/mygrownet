<?php

namespace App\Domain\PrimeEdge\Exceptions;

class ComplianceDeadlineExceededException extends PrimeEdgeException
{
    public function __construct(string $taskId = '', string $deadline = '')
    {
        parent::__construct(
            message: "Compliance deadline exceeded for task {$taskId} (was due: {$deadline})",
            errorCode: 'COMPLIANCE_DEADLINE_EXCEEDED',
            context: ['task_id' => $taskId, 'deadline' => $deadline]
        );
    }
}
