<?php

namespace App\Console\Commands\BizBoost;

use App\Jobs\BizBoost\PublishScheduledPostsJob;
use Illuminate\Console\Command;

class PublishScheduledPostsCommand extends Command
{
    protected $signature = 'bizboost:publish-scheduled';
    protected $description = 'Publish all scheduled BizBoost posts that are due';

    public function handle(): int
    {
        $this->info('Dispatching scheduled posts job...');
        
        PublishScheduledPostsJob::dispatch();
        
        $this->info('Job dispatched successfully.');
        
        return Command::SUCCESS;
    }
}
