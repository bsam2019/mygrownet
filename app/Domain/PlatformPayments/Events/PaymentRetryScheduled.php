<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;

class PaymentRetryScheduled extends PlatformEvent
{
    public const NAME = 'platform.payment.retry_scheduled.v1';

    public function __construct(
        public readonly int $transactionId,
        public readonly int $organizationId,
        public readonly int $attemptNumber,
        public readonly \DateTimeImmutable $scheduledAt,
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
            'attempt_number' => $this->attemptNumber,
            'scheduled_at' => $this->scheduledAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
