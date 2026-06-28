<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Exceptions;

class DuplicateEmployeeException extends GrowBizException
{
    public function __construct(string $email)
    {
        parent::__construct(
            message: "An employee with email '{$email}' already exists.",
            errorCode: 'DUPLICATE_EMPLOYEE',
            context: ['email' => $email],
            code: 422
        );
    }
}
