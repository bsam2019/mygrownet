<?php

namespace App\Domain\GrowFinance\Events;

use App\Domain\Core\Events\PlatformEvent;

class ReportGenerated extends PlatformEvent
{
    public const NAME = 'growfinance.report.generated.v1';

    public function __construct(
        public readonly int $companyId,
        public readonly string $reportType,
        public readonly string $reportFormat,
        public readonly string $reportUrl,
        public readonly \DateTimeImmutable $generatedAt,
    ) {
        parent::__construct(
            entityId: "{$companyId}/{$reportType}/{$generatedAt->format('YmdHis')}",
            eventName: self::NAME,
        );
    }

    public function toPayload(): array
    {
        return [
            'company_id' => $this->companyId,
            'report_type' => $this->reportType,
            'report_format' => $this->reportFormat,
            'report_url' => $this->reportUrl,
            'generated_at' => $this->generatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
