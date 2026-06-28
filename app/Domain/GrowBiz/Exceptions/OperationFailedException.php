<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Exceptions;

class OperationFailedException extends GrowBizException
{
    public function __construct(string $operation, string $reason, array $context = [])
    {
        parent::__construct(
            message: "Operation '{$operation}' failed: {$reason}",
            errorCode: 'OPERATION_FAILED',
            context: array_merge(['operation' => $operation], $context),
            code: 500
        );
    }
}
