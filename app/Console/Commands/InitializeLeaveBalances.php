<?php

namespace App\Console\Commands;

use App\Domain\CMS\Core\Services\LeaveManagementService;
use App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel;
use App\Infrastructure\Persistence\Eloquent\CMS\LeaveTypeModel;
use Illuminate\Console\Command;

class InitializeLeaveBalances extends Command
{
    protected $signature = 'cms:initialize-leave-balances 
                            {--company= : Specific company ID to initialize}
                            {--force : Force initialization even if balances exist}';

    protected $description = 'Initialize leave balances for all active workers';

    public function __construct(
        private LeaveManagementService $leaveService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Initializing leave balances for workers...');

        $companyId = $this->option('company');
        $force = $this->option('force');

        // Get all active workers
        $query = WorkerModel::where('employment_status', 'active');
        
        if ($companyId) {
            $query->where('company_id', $companyId);
            $this->info("Filtering by company ID: {$companyId}");
        }

        $workers = $query->get();
        $this->info("Found {$workers->count()} active workers");

        if ($workers->isEmpty()) {
            $this->warn('No active workers found');
            return Command::SUCCESS;
        }

        $initialized = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($workers->count());
        $progressBar->start();

        foreach ($workers as $worker) {
            try {
                // Check if worker already has leave balances
                if (!$force && $worker->leaveBalances()->exists()) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                // Get all leave types for the company
                $leaveTypes = LeaveTypeModel::where('company_id', $worker->company_id)
                    ->where('is_active', true)
                    ->get();

                if ($leaveTypes->isEmpty()) {
                    $this->newLine();
                    $this->warn("No leave types found for company {$worker->company_id}");
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                // Initialize balances for each leave type
                foreach ($leaveTypes as $leaveType) {
                    $this->leaveService->initializeLeaveBalance(
                        $worker->id,
                        $leaveType->id,
                        date('Y')
                    );
                }

                $initialized++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error initializing balances for worker {$worker->id}: {$e->getMessage()}");
                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Leave Balance Initialization Complete');
        $this->table(
            ['Status', 'Count'],
            [
                ['Initialized', $initialized],
                ['Skipped', $skipped],
                ['Errors', $errors],
                ['Total', $workers->count()],
            ]
        );

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
