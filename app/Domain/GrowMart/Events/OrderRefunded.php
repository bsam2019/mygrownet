<?php

namespace App\Domain\GrowMart\Events;

use App\Domain\Core\Events\PlatformEvent;

class OrderRefunded extends PlatformEvent
{
    public const NAME = 'growmart.order.refunded.v1';

    public function __construct(
        public readonly int $orderId,
        public readonly int $companyId,
        public readonly float $refundAmount,
        public readonly string $currency,
        public readonly string $reason,
        public readonly \DateTimeImmutable $refundedAt,
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
            'refund_amount' => $this->refundAmount,
            'currency' => $this->currency,
            'reason' => $this->reason,
            'refunded_at' => $this->refundedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
