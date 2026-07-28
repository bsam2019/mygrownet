<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;
use App\Domain\Core\ValueObjects\PlatformContext;
use Illuminate\Support\Str;

class PaymentRefunded extends PlatformEvent
{
    public const NAME = 'platform.payment.refunded.v1';

    public function __construct(
        public readonly int $transactionId,
        public readonly int $organizationId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $refundReference,
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
                'amount' => $amount,
                'currency' => $currency,
                'refund_reference' => $refundReference,
            ],
        );
    }

    public function toPayload(): array
    {
        return $this->payload;
    }
}
