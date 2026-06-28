<?php

namespace App\Console\Commands;

use App\Application\Services\LoyaltyReward\LgrCycleService;
use Illuminate\Console\Command;

class LgrCompleteExpiredCycles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lgr:complete-expired-cycles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete expired LGR cycles and prepare for new cycles';

    public function __construct(
        private LgrCycleService $cycleService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired LGR cycles...');

        $count = $this->cycleService->checkAndCompleteExpiredCycles();

        if ($count > 0) {
            $this->info("Completed {$count} expired LGR cycle(s).");
        } else {
            $this->info('No expired cycles found.');
        }

        return Command::SUCCESS;
    }
}
