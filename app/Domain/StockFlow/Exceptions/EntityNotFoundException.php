<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Exceptions;

class EntityNotFoundException extends StockFlowException
{
    public function __construct(string $entityType, int $id)
    {
        parent::__construct(
            message: "{$entityType} #{$id} not found",
            errorCode: 'ENTITY_NOT_FOUND',
            context: ['entity_type' => $entityType, 'id' => $id],
            code: 404
        );
    }
}
