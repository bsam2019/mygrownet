<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;
use App\Domain\Core\ValueObjects\PlatformContext;
use Illuminate\Support\Str;

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
                'provider_transaction_id' => $providerTransactionId,
            ],
        );
    }

    public function toPayload(): array
    {
        return $this->payload;
    }
}
