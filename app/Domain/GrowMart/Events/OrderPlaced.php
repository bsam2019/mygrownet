<?php

namespace App\Domain\GrowMart\Events;

use App\Domain\Core\Events\PlatformEvent;

class OrderPlaced extends PlatformEvent
{
    public const NAME = 'growmart.order.placed.v1';

    public function __construct(
        public readonly int $orderId,
        public readonly int $companyId,
        public readonly int $customerId,
        public readonly float $total,
        public readonly string $currency,
        public readonly int $itemCount,
    ) {
        parent::__construct(
            entityId: (string) $orderId,
            eventName: self::NAME,
        );
    }

    public function toPayload(): array
    {
        return [
            'order_id' => $this->orderId,
            'company_id' => $this->companyId,
            'customer_id' => $this->customerId,
            'total' => $this->total,
            'currency' => $this->currency,
            'item_count' => $this->itemCount,
        ];
    }
}
