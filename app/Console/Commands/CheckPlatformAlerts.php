<?php

namespace App\Console\Commands;

use App\Domain\Core\Services\AlertService;
use App\Domain\Core\Services\DeadLetterService;
use Illuminate\Console\Command;

class CheckPlatformAlerts extends Command
{
    protected $signature = 'platform:check-alerts';
    protected $description = 'Check platform alert thresholds and fire alerts';

    public function handle(AlertService $alerts): int
    {
        $this->info('Checking platform alerts...');

        $fired = $alerts->checkAll();

        $failureAlert = $alerts->checkFailureRate();
        $dlqAlert = $alerts->checkDeadLetterQueue();
        $backlogAlert = $alerts->checkQueueBacklog();

        $results = array_filter([$failureAlert, $dlqAlert, $backlogAlert]);

        if (empty($results)) {
            $this->info('All alert checks passed.');
            return 0;
        }

        foreach ($results as $alert) {
            $this->warn("[{$alert['severity']}] {$alert['message']}");
        }

        return 1;
    }
}
