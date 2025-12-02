<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Exceptions;

class InvalidStatusTransitionException extends GrowBizException
{
    public function __construct(string $entity, string $currentStatus, string $newStatus)
    {
        parent::__construct(
            message: "Invalid status transition for {$entity}: cannot change from '{$currentStatus}' to '{$newStatus}'.",
            errorCode: 'INVALID_STATUS_TRANSITION',
            context: [
                'entity' => $entity,
                'current_status' => $currentStatus,
                'new_status' => $newStatus,
            ],
            code: 422
        );
    }
}
