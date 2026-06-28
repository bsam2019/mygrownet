<?php

namespace App\Console\Commands;

use App\Services\PlatformLoanService;
use Illuminate\Console\Command;

class UpdateLoanRiskCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:update-risk-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update risk categories for all active platform loans based on overdue status';

    /**
     * Execute the console command.
     */
    public function handle(PlatformLoanService $loanService): int
    {
        $this->info('Updating loan risk categories...');
        
        try {
            $loanService->updateAllRiskCategories();
            
            $this->info('✓ Loan risk categories updated successfully');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Failed to update loan risk categories: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
