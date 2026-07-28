<?php

namespace App\Domain\GrowFinance\Events;

class PeriodClosed
{
    public const NAME = 'growfinance.period.closed.v1';

    public function __construct(
        public readonly int $companyId,
        public readonly string $periodType,
        public readonly \DateTimeImmutable $periodStart,
        public readonly \DateTimeImmutable $periodEnd,
        public readonly \DateTimeImmutable $closedAt,
    ) {}

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
