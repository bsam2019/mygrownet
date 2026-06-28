<?php

namespace App\Console\Commands;

use App\Services\StarterKitService;
use Illuminate\Console\Command;

class ProcessStarterKitUnlocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starter-kit:process-unlocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process daily starter kit content unlocks';

    /**
     * Execute the console command.
     */
    public function handle(StarterKitService $starterKitService): int
    {
        $this->info('Processing starter kit unlocks...');

        $unlocked = $starterKitService->processUnlocks();

        $this->info("Unlocked {$unlocked} content items.");

        // Also expire shop credits
        $expired = $starterKitService->expireShopCredits();

        $this->info("Expired {$expired} shop credits.");

        return Command::SUCCESS;
    }
}
