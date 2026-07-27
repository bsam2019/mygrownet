<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;

class PaymentSettled extends PlatformEvent
{
    public const NAME = 'platform.payment.settled.v1';

    public function __construct(
        public readonly int $transactionId,
        public readonly int $organizationId,
        public readonly float $settledAmount,
        public readonly float $fee,
        public readonly string $currency,
    ) {
        parent::__construct(
            entityId: (string) $transactionId,
            eventName: self::NAME,
        );
    }

    public function toPayload(): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'organization_id' => $this->organizationId,
            'settled_amount' => $this->settledAmount,
            'fee' => $this->fee,
            'currency' => $this->currency,
        ];
    }
}
