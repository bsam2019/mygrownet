<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;
use App\Domain\Core\ValueObjects\PlatformContext;
use Illuminate\Support\Str;

class SettlementReconciled extends PlatformEvent
{
    public const NAME = 'platform.payment.settlement_reconciled.v1';

    public function __construct(
        public readonly int $settlementId,
        public readonly int $organizationId,
        public readonly float $expectedAmount,
        public readonly float $actualAmount,
        public readonly string $currency,
        public readonly string $status,
    ) {
        parent::__construct(
            eventId: (string) Str::uuid(),
            eventName: self::NAME,
            eventVersion: '1.0',
            publisher: 'platform-payments',
            occurredAt: new \DateTimeImmutable(),
            correlationId: (string) $settlementId,
            causationId: null,
            context: PlatformContext::make(
                userId: (string) $organizationId,
                organizationId: (string) $organizationId,
                applicationId: 'platform-payments',
            ),
            payload: [
                'settlement_id' => $settlementId,
                'organization_id' => $organizationId,
                'expected_amount' => $expectedAmount,
                'actual_amount' => $actualAmount,
                'currency' => $currency,
                'status' => $status,
            ],
        );
    }

    public function toPayload(): array
    {
        return $this->payload;
    }
}
