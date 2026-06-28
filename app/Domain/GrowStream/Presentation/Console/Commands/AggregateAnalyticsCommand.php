<?php

namespace App\Domain\GrowStream\Presentation\Console\Commands;

use App\Domain\GrowStream\Infrastructure\Jobs\AggregateAnalyticsJob;
use Illuminate\Console\Command;

class AggregateAnalyticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'growstream:aggregate-analytics {--date= : Date to aggregate (YYYY-MM-DD)}';

    /**
     * The console command description.
     */
    protected $description = 'Aggregate GrowStream analytics for a specific date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date') ?? now()->subDay()->toDateString();

        $this->info("Aggregating analytics for {$date}...");

        AggregateAnalyticsJob::dispatch($date);

        $this->info("Analytics aggregation job dispatched successfully!");
        $this->comment("The job will process in the background.");

        return Command::SUCCESS;
    }
}
