<?php

namespace App\Jobs\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishScheduledPostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Get all posts that are scheduled and ready to publish
        $posts = BizBoostPostModel::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->with(['business.integrations', 'media'])
            ->get();

        foreach ($posts as $post) {
            try {
                // Dispatch individual publish job for each post
                PublishToSocialMediaJob::dispatch($post);
                
                Log::info("Dispatched publish job for post {$post->id}");
            } catch (\Exception $e) {
                Log::error("Failed to dispatch publish job for post {$post->id}: " . $e->getMessage());
            }
        }

        Log::info("Processed {$posts->count()} scheduled posts");
    }
}
