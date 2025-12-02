<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Exceptions;

class UnauthorizedAccessException extends GrowBizException
{
    public function __construct(string $resource, int $resourceId, int $userId)
    {
        parent::__construct(
            message: "Unauthorized access to {$resource} with ID {$resourceId}.",
            errorCode: 'UNAUTHORIZED_ACCESS',
            context: [
                'resource' => $resource,
                'resource_id' => $resourceId,
                'user_id' => $userId,
            ],
            code: 403
        );
    }
}
