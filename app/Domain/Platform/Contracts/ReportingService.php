<?php

namespace App\Domain\Platform\Contracts;

interface ReportingService
{
    public function generate(string $reportType, array $params = []): string;
    public function schedule(string $reportType, string $cron, array $params = []): void;
    public function export(string $reportId, string $format = 'pdf'): string;
}
