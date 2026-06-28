<?php

namespace App\Console\Commands\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostCampaignModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use App\Jobs\BizBoost\CampaignSequenceJob;
use App\Jobs\BizBoost\PublishPostToSocialMediaJob;
use Illuminate\Console\Command;

class ProcessCampaignsCommand extends Command
{
    protected $signature = 'bizboost:process-campaigns';

    protected $description = 'Process active BizBoost campaigns and schedule daily posts';

    public function handle(): int
    {
        $this->processCampaigns();
        $this->processScheduledPosts();

        return Command::SUCCESS;
    }

    private function processCampaigns(): void
    {
        $campaigns = BizBoostCampaignModel::where('status', 'active')
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No active campaigns to process.');
            return;
        }

        $this->info("Found {$campaigns->count()} active campaign(s) to process.");

        foreach ($campaigns as $campaign) {
            try {
                // Dispatch the campaign sequence job to schedule today's posts
                CampaignSequenceJob::dispatch($campaign);
                $this->line("  → Dispatched sequence job for campaign #{$campaign->id}: {$campaign->name}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to dispatch job for campaign #{$campaign->id}: {$e->getMessage()}");
            }
        }

        $this->info('Done processing campaigns.');
    }

    private function processScheduledPosts(): void
    {
        // Find posts scheduled to be published now
        $posts = BizBoostPostModel::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No scheduled posts ready to publish.');
            return;
        }

        $this->info("Found {$posts->count()} scheduled post(s) ready to publish.");

        foreach ($posts as $post) {
            try {
                $platforms = $post->platform_targets ?? [];

                if (empty($platforms)) {
                    $this->warn("  ⚠ Post #{$post->id} has no target platforms");
                    continue;
                }

                foreach ($platforms as $platform) {
                    PublishPostToSocialMediaJob::dispatch($post, $platform);
                    $this->line("  → Dispatched publish job for post #{$post->id} to {$platform}");
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to dispatch job for post #{$post->id}: {$e->getMessage()}");
            }
        }

        $this->info('Done processing scheduled posts.');
    }
}
          