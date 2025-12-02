<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Exceptions;

class EmployeeNotFoundException extends GrowBizException
{
    public function __construct(int $employeeId)
    {
        parent::__construct(
            message: "Employee with ID {$employeeId} not found.",
            errorCode: 'EMPLOYEE_NOT_FOUND',
            context: ['employee_id' => $employeeId],
            code: 404
        );
    }
}
