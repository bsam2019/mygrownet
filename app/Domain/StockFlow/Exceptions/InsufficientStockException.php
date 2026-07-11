<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Exceptions;

class InsufficientStockException extends StockFlowException
{
    public function __construct(int $itemId, float $requested, float $available)
    {
        parent::__construct(
            message: "Insufficient stock for item #{$itemId}: requested {$requested}, available {$available}",
            errorCode: 'INSUFFICIENT_STOCK',
            context: ['item_id' => $itemId, 'requested' => $requested, 'available' => $available],
            code: 400
        );
    }
}
