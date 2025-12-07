<?php

namespace App\Console\Commands\BizBoost;

use App\Jobs\BizBoost\AggregateAnalyticsJob;
use Illuminate\Console\Command;

class AggregateAnalyticsCommand extends Command
{
    protected $signature = 'bizboost:aggregate-analytics 
                            {--date= : Specific date to aggregate (Y-m-d format)}
                            {--business= : Specific business ID to aggregate}';
    
    protected $description = 'Aggregate BizBoost analytics events into daily summaries';

    public function handle(): int
    {
        $date = $this->option('date');
        $businessId = $this->option('business');

        $this->info('Aggregating BizBoost analytics...');

        if ($date) {
            $this->info("Processing date: {$date}");
        } else {
            $this->info('Processing yesterday\'s data');
        }

        if ($businessId) {
            $this->info("Processing business ID: {$businessId}");
        } else {
            $this->info('Processing all active businesses');
        }

        AggregateAnalyticsJob::dispatch(
            $businessId ? (int) $businessId : null,
            $date
        );

        $this->info('Analytics aggregation job dispatched.');

        return Command::SUCCESS;
    }
}
