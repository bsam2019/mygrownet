<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;
use App\Domain\Core\ValueObjects\PlatformContext;
use Illuminate\Support\Str;

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
                'settled_amount' => $settledAmount,
                'fee' => $fee,
                'currency' => $currency,
            ],
        );
    }

    public function toPayload(): array
    {
        return $this->payload;
    }
}
