<?php

namespace App\Domain\PlatformPayments\Events;

use App\Domain\Core\Events\PlatformEvent;

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
            entityId: (string) $settlementId,
            eventName: self::NAME,
        );
    }

    public function toPayload(): array
    {
        return [
            'settlement_id' => $this->settlementId,
            'organization_id' => $this->organizationId,
            'expected_amount' => $this->expectedAmount,
            'actual_amount' => $this->actualAmount,
            'currency' => $this->currency,
            'status' => $this->status,
        ];
    }
}
