<?php

namespace App\Domain\GrowStream\Presentation\Console\Commands;

use App\Domain\GrowStream\Infrastructure\Jobs\ProcessVideoJob;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use Illuminate\Console\Command;

class ProcessPendingVideosCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'growstream:process-pending-videos';

    /**
     * The console command description.
     */
    protected $description = 'Process all pending videos that are stuck in processing state';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Finding pending videos...");

        // Find videos that are stuck in processing or uploading state
        $pendingVideos = Video::whereIn('upload_status', ['uploading', 'processing'])
            ->where('created_at', '<', now()->subHours(2)) // Older than 2 hours
            ->get();

        if ($pendingVideos->isEmpty()) {
            $this->info("No pending videos found.");
            return Command::SUCCESS;
        }

        $this->info("Found {$pendingVideos->count()} pending videos.");

        $bar = $this->output->createProgressBar($pendingVideos->count());
        $bar->start();

        foreach ($pendingVideos as $video) {
            ProcessVideoJob::dispatch($video->id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Processing jobs dispatched for {$pendingVideos->count()} videos!");

        return Command::SUCCESS;
    }
}
