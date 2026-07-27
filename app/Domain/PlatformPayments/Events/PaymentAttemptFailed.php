<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;

class PaymentAttemptFailed extends PlatformEvent
{
    public const NAME = 'platform.payment.failed.v1';

    public function __construct(
        public readonly int $transactionId,
        public readonly int $organizationId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $failureReason,
        public readonly int $attemptNumber,
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
            'failure_reason' => $this->failureReason,
            'attempt_number' => $this->attemptNumber,
        ];
    }
}
