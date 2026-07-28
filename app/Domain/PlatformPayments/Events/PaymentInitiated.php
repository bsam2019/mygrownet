<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;
use Illuminate\Support\Str;

class PaymentInitiated extends PlatformEvent
{
    public const NAME = 'platform.payment.initiated.v1';

    public function __construct(
        public readonly int $transactionId,
        public readonly int $organizationId,
        public readonly float $amount,
        public readonly string $currency,
        public readonly string $paymentMethod,
    ) {
        parent::__construct(
            eventId: (string) Str::uuid(),
            eventName: self::NAME,
            eventVersion: '1.0',
            publisher: 'platform-payments',
            occurredAt: new \DateTimeImmutable(),
            correlationId: (string) $transactionId,
            causationId: null,
            context: \App\Domain\Core\ValueObjects\PlatformContext::make(
                userId: (string) $organizationId,
                organizationId: (string) $organizationId,
                applicationId: 'platform-payments',
            ),
            payload: [
                'transaction_id' => $transactionId,
                'organization_id' => $organizationId,
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => $paymentMethod,
            ],
        );
    }

    public function toPayload(): array
    {
        return $this->payload;
    }
}
