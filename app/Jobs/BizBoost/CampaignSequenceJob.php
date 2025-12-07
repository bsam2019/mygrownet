<?php

namespace App\Jobs\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostCampaignModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CampaignSequenceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private int $campaignId
    ) {}

    public function handle(): void
    {
        $campaign = BizBoostCampaignModel::with(['business', 'posts'])->find($this->campaignId);

        if (!$campaign) {
            Log::warning("Campaign not found: {$this->campaignId}");
            return;
        }

        if ($campaign->status !== 'active') {
            Log::info("Campaign {$this->campaignId} is not active, skipping");
            return;
        }

        // Check if campaign has ended
        if (now()->gt($campaign->end_date)) {
            $campaign->update(['status' => 'completed']);
            Log::info("Campaign {$this->campaignId} completed");
            return;
        }

        // Calculate current day of campaign
        $currentDay = now()->diffInDays($campaign->start_date) + 1;

        // Get posts scheduled for today
        $todaysPosts = $campaign->posts()
            ->wherePivot('sequence_day', $currentDay)
            ->where('status', 'draft')
            ->get();

        foreach ($todaysPosts as $post) {
            $this->schedulePost($post, $campaign);
        }

        // Update campaign stats
        $campaign->update([
            'posts_created' => $campaign->posts()->count(),
            'posts_published' => $campaign->posts()->where('status', 'published')->count(),
        ]);
    }

    private function schedulePost(BizBoostPostModel $post, BizBoostCampaignModel $campaign): void
    {
        // Get optimal posting time from campaign config
        $config = $campaign->campaign_config ?? [];
        $postingTimes = $config['posting_times'] ?? ['09:00', '12:00', '18:00'];
        
        // Pick a random time from the configured times
        $time = $postingTimes[array_rand($postingTimes)];
        $scheduledAt = now()->setTimeFromTimeString($time);

        // If time has passed today, schedule for tomorrow
        if ($scheduledAt->lt(now())) {
            $scheduledAt = $scheduledAt->addDay();
        }

        $post->update([
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
            'platform_targets' => $campaign->target_platforms,
        ]);

        Log::info("Scheduled campaign post {$post->id} for {$scheduledAt}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("CampaignSequenceJob failed for campaign {$this->campaignId}: " . $exception->getMessage());
    }
}
