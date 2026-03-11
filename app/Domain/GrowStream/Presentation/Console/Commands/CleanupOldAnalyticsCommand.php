<?php

namespace App\Domain\GrowStream\Presentation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOldAnalyticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'growstream:cleanup-analytics {--days=90 : Number of days to keep}';

    /**
     * The console command description.
     */
    protected $description = 'Cleanup old analytics data older than specified days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days)->toDateString();

        $this->info("Cleaning up analytics data older than {$cutoffDate}...");

        if (!$this->confirm("This will delete analytics data older than {$days} days. Continue?")) {
            $this->info("Cleanup cancelled.");
            return Command::SUCCESS;
        }

        // Delete old daily analytics
        $deletedDaily = DB::table('growstream_video_analytics_daily')
            ->where('date', '<', $cutoffDate)
            ->delete();

        $this->info("Deleted {$deletedDaily} daily analytics records.");

        // Optionally delete old view records (keep raw data longer)
        if ($this->confirm("Also delete raw view data older than {$days} days?")) {
            $deletedViews = DB::table('growstream_video_views')
                ->where('created_at', '<', now()->subDays($days))
                ->delete();

            $this->info("Deleted {$deletedViews} view records.");
        }

        $this->info("Cleanup completed successfully!");

        return Command::SUCCESS;
    }
}
