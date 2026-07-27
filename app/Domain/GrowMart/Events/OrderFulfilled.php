<?php

namespace App\Domain\GrowMart\Events;

use App\Domain\Core\Events\PlatformEvent;

class OrderFulfilled extends PlatformEvent
{
    public const NAME = 'growmart.order.fulfilled.v1';

    public function __construct(
        public readonly int $orderId,
        public readonly int $companyId,
        public readonly string $fulfillmentStatus,
        public readonly ?\DateTimeImmutable $fulfilledAt,
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
            'fulfillment_status' => $this->fulfillmentStatus,
            'fulfilled_at' => $this->fulfilledAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
