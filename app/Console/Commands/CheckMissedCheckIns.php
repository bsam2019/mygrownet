<?php

namespace App\Console\Commands;

use App\Domain\Ubumi\Services\AlertService;
use Illuminate\Console\Command;

class CheckMissedCheckIns extends Command
{
    protected $signature = 'ubumi:check-missed-checkins';
    protected $description = 'Check for missed check-ins and send alerts';

    public function handle(AlertService $alertService): int
    {
        $this->info('Checking for missed check-ins...');
        
        $alertService->checkMissedCheckIns();
        
        $this->info('Missed check-in check completed.');
        
        return Command::SUCCESS;
    }
}
