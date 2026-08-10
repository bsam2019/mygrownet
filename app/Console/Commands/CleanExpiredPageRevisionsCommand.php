<?php

namespace App\Console\Commands;

use App\Services\GrowBuilder\PageRevisionService;
use Illuminate\Console\Command;

class CleanExpiredPageRevisionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'growbuilder:revisions:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired GrowBuilder page revisions (older than 90 days)';

    /**
     * Execute the console command.
     */
    public function handle(PageRevisionService $revisionService): int
    {
        $this->info('Cleaning up expired GrowBuilder page revisions...');

        $deletedCount = $revisionService->cleanupExpired();

        $this->info("Deleted {$deletedCount} expired revision(s).");

        return Command::SUCCESS;
    }
}
