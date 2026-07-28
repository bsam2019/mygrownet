<?php

namespace App\Domain\GrowFinance\Events;

class ReportGenerated
{
    public const NAME = 'growfinance.report.generated.v1';

    public function __construct(
        public readonly int $companyId,
        public readonly string $reportType,
        public readonly \DateTimeImmutable $generatedAt,
        public readonly ?\DateTimeImmutable $periodStart = null,
        public readonly ?\DateTimeImmutable $periodEnd = null,
        public readonly string $reportFormat = 'pdf',
        public readonly string $reportUrl = '',
    ) {}

    public function toPayload(): array
    {
        return [
            'company_id' => $this->companyId,
            'report_type' => $this->reportType,
            'report_format' => $this->reportFormat,
            'report_url' => $this->reportUrl,
            'period_start' => $this->periodStart?->format('Y-m-d'),
            'period_end' => $this->periodEnd?->format('Y-m-d'),
            'generated_at' => $this->generatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
