<?php

namespace App\Console\Commands\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use App\Jobs\BizBoost\PublishToSocialMediaJob;
use Illuminate\Console\Command;

class ProcessScheduledPostsCommand extends Command
{
    protected $signature = 'bizboost:process-scheduled-posts';

    protected $description = 'Process and publish BizBoost posts that are scheduled and ready';

    public function handle(): int
    {
        $posts = BizBoostPostModel::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->with(['business.integrations', 'media'])
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No scheduled posts ready for publishing.');
            return Command::SUCCESS;
        }

        $this->info("Found {$posts->count()} post(s) ready to publish.");

        foreach ($posts as $post) {
            try {
                PublishToSocialMediaJob::dispatch($post);
                $this->line("  → Dispatched publish job for post #{$post->id}: {$post->title}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to dispatch job for post #{$post->id}: {$e->getMessage()}");
            }
        }

        $this->info('Done processing scheduled posts.');

        return Command::SUCCESS;
    }
}
