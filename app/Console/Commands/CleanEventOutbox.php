<?php

namespace App\Console\Commands;

use App\Domain\Core\Services\OutboxService;
use Illuminate\Console\Command;

class CleanEventOutbox extends Command
{
    protected $signature = 'platform:clean-outbox
        {--days=7 : Archive published events older than this many days}';

    protected $description = 'Archive old published events from the outbox';

    public function handle(OutboxService $outbox): int
    {
        $days = (int) $this->option('days');
        $deleted = $outbox->archive($days);

        $this->info("Archived {$deleted} published events older than {$days} days.");
        return 0;
    }
}
