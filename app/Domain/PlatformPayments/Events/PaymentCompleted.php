<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;

class PaymentCompleted extends PlatformEvent
{
    public const NAME = 'platform.payment.completed.v1';

    public function __construct(
        public readonly int $transactionId,
        public readonly int $organizationId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $providerTransactionId,
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
            'amount' => $this->amount,
            'currency' => $this->currency,
            'provider_transaction_id' => $this->providerTransactionId,
        ];
    }
}
