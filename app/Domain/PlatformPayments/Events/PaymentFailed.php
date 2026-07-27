<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;

class PaymentFailed extends PlatformEvent
{
    public const NAME = 'platform.payment.collection_failed.v1';

    public function __construct(
        public readonly int $transactionId,
        public readonly int $organizationId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $failureReason,
        public readonly int $attemptCount,
        public readonly ?int $subscriptionId = null,
    ) {
        parent::__construct(
            entityId: (string) $transactionId,
            eventName: self::NAME,
        );
    }

    public function toPayload(): array
    {
        return array_filter([
            'transaction_id' => $this->transactionId,
            'organization_id' => $this->organizationId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'failure_reason' => $this->failureReason,
            'attempt_count' => $this->attemptCount,
            'subscription_id' => $this->subscriptionId,
        ], fn($v) => $v !== null);
    }
}
