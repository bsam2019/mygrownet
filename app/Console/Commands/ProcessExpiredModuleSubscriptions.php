<?php

namespace App\Console\Commands;

use App\Application\UseCases\Module\ProcessExpiredSubscriptionsUseCase;
use Illuminate\Console\Command;

class ProcessExpiredModuleSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expired module subscriptions (renew or expire)';

    /**
     * Execute the console command.
     */
    public function handle(ProcessExpiredSubscriptionsUseCase $useCase): int
    {
        $this->info('Processing expired module subscriptions...');

        $stats = $useCase->execute();

        $this->info("Checked: {$stats['total_checked']} subscriptions");
        $this->info("Renewed: {$stats['renewed']} subscriptions");
        $this->info("Expired: {$stats['expired']} subscriptions");

        if ($stats['failed'] > 0) {
            $this->warn("Failed: {$stats['failed']} subscriptions");
            foreach ($stats['errors'] as $error) {
                $this->error("  - {$error['subscription_id']}: {$error['error']}");
            }
        }

        $this->info('Done!');

        return Command::SUCCESS;
    }
}
