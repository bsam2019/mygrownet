<?php

namespace App\Console\Commands;

use App\Domain\BMS\Core\Services\AnalyticsService;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use Illuminate\Console\Command;

class CalculateDailyProductivityMetrics extends Command
{
    protected $signature = 'operations:calculate-productivity {--date= : Date to calculate (default: yesterday)}';
    protected $description = 'Calculate daily productivity metrics for all companies';

    public function handle(AnalyticsService $analyticsService): int
    {
        $date = $this->option('date') ?? now()->subDay()->toDateString();
        
        $this->info("Calculating productivity metrics for {$date}...");

        $companies = CompanyModel::where('has_operations_module', true)->get();

        foreach ($companies as $company) {
            $this->info("Processing company: {$company->name}");
            
            try {
                $analyticsService->calculateDailyProductivityMetrics($company->id, $date);
                $analyticsService->calculateCompletionTrends($company->id, $date, 'daily');
                
                $this->info("✓ Completed for {$company->name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed for {$company->name}: {$e->getMessage()}");
            }
        }

        $this->info('Productivity metrics calculation completed!');

        return Command::SUCCESS;
    }
}
