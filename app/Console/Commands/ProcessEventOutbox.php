<?php

namespace App\Console\Commands;

use App\Domain\Core\Services\OutboxService;
use Illuminate\Console\Command;

class ProcessEventOutbox extends Command
{
    protected $signature = 'platform:process-outbox
        {--batch=50 : Number of pending events to process per run}';

    protected $description = 'Publish pending events from the outbox';

    public function handle(OutboxService $outbox): int
    {
        $batch = (int) $this->option('batch');
        $pending = $outbox->pendingCount();

        if ($pending === 0) {
            $this->info('No pending events in outbox.');
            return 0;
        }

        $this->info("Processing {$pending} pending events...");

        $results = $outbox->publishPending($batch);

        $this->line("Published: {$results['published']}, Failed: {$results['failed']}");

        if ($results['failed'] > 0) {
            $this->warn('Some events failed. Check logs for details.');
            return 1;
        }

        $this->info('Outbox processed successfully.');
        return 0;
    }
}
