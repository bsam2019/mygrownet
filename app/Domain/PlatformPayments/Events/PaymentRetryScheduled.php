<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;
use App\Domain\Core\ValueObjects\PlatformContext;
use Illuminate\Support\Str;

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
            eventId: (string) Str::uuid(),
            eventName: self::NAME,
            eventVersion: '1.0',
            publisher: 'platform-payments',
            occurredAt: new \DateTimeImmutable(),
            correlationId: (string) $transactionId,
            causationId: null,
            context: PlatformContext::make(
                userId: (string) $organizationId,
                organizationId: (string) $organizationId,
                applicationId: 'platform-payments',
            ),
            payload: [
                'transaction_id' => $transactionId,
                'organization_id' => $organizationId,
                'attempt_number' => $attemptNumber,
                'scheduled_at' => $scheduledAt->format(\DateTimeInterface::ATOM),
            ],
        );
    }

    public function toPayload(): array
    {
        return $this->payload;
    }
}
