<?php

namespace App\Console\Commands;

use App\Domain\GrowFinance\Services\ReportSnapshotService;
use DateTimeImmutable;
use Illuminate\Console\Command;

class SnapshotReports extends Command
{
    protected $signature = 'growfinance:snapshot-reports {business_id} {--period-start=} {--period-end=}';
    protected $description = 'Snapshot all financial reports for a period';

    public function handle(ReportSnapshotService $snapshotService): int
    {
        $businessId = (int) $this->argument('business_id');
        $periodStart = new DateTimeImmutable($this->option('period-start') ?? 'first day of this month');
        $periodEnd = new DateTimeImmutable($this->option('period-end') ?? 'last day of this month');

        $this->info("Snapshotting reports for business $businessId from {$periodStart->format('Y-m-d')} to {$periodEnd->format('Y-m-d')}...");

        $results = $snapshotService->snapshotAll($businessId, $periodStart, $periodEnd);

        foreach ($results as $type => $id) {
            $this->line("  $type: snapshot #$id");
        }

        $this->info('Reports snapshot complete!');
        return 0;
    }
}
