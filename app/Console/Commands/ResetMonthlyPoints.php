<?php

namespace App\Console\Commands;

use App\Services\PointService;
use Illuminate\Console\Command;

class ResetMonthlyPoints extends Command
{
    protected $signature = 'points:reset-monthly';
    protected $description = 'Reset monthly activity points for all users (run on 1st of each month)';

    public function handle(PointService $pointService): int
    {
        $this->info('Starting monthly points reset...');

        try {
            $pointService->resetMonthlyPoints();
            $this->info('✓ Monthly points reset completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Error resetting monthly points: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
