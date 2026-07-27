<?php

namespace App\Domain\GrowFinance\Events;

use App\Domain\Core\Events\PlatformEvent;

class PeriodClosed extends PlatformEvent
{
    public const NAME = 'growfinance.period.closed.v1';

    public function __construct(
        public readonly int $companyId,
        public readonly string $periodType,
        public readonly \DateTimeImmutable $periodStart,
        public readonly \DateTimeImmutable $periodEnd,
        public readonly \DateTimeImmutable $closedAt,
    ) {
        parent::__construct(
            entityId: "{$companyId}/{$periodType}/{$periodStart->format('Y-m')}",
            eventName: self::NAME,
        );
    }

    public function toPayload(): array
    {
        return [
            'company_id' => $this->companyId,
            'period_type' => $this->periodType,
            'period_start' => $this->periodStart->format('Y-m-d'),
            'period_end' => $this->periodEnd->format('Y-m-d'),
            'closed_at' => $this->closedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
