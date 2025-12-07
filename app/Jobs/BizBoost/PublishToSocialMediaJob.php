<?php

namespace App\Jobs\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use App\Domain\BizBoost\Services\FacebookGraphService;
use App\Domain\BizBoost\Services\InstagramGraphService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishToSocialMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public BizBoostPostModel $post
    ) {}

    public function handle(
        FacebookGraphService $facebookService,
        InstagramGraphService $instagramService
    ): void {
        $post = $this->post->fresh(['business.integrations', 'media']);
        
        if (!$post || $post->status !== 'scheduled') {
            Log::info("Post {$this->post->id} is no longer scheduled, skipping.");
            return;
        }

        // Mark as publishing
        $post->update(['status' => 'publishing']);

        $platforms = $post->platform_targets ?? [];
        $externalIds = [];
        $errors = [];

        foreach ($platforms as $platform) {
            try {
                $integration = $post->business->integrations()
                    ->where('provider', $platform)
                    ->where('status', 'active')
                    ->first();

                if (!$integration) {
                    $errors[] = "{$platform}: No active integration found";
                    continue;
                }

                $result = match ($platform) {
                    'facebook' => $facebookService->publishPost($post, $integration),
                    'instagram' => $instagramService->publishPost($post, $integration),
                    default => throw new \Exception("Unsupported platform: {$platform}"),
                };

                if ($result['success']) {
                    $externalIds[$platform] = $result['post_id'];
                    
                    // Update last used
                    $integration->update(['last_used_at' => now()]);
                } else {
                    $errors[] = "{$platform}: " . ($result['error'] ?? 'Unknown error');
                }

            } catch (\Exception $e) {
                $errors[] = "{$platform}: " . $e->getMessage();
                Log::error("Failed to publish to {$platform} for post {$post->id}: " . $e->getMessage());
            }
        }

        // Update post status
        if (empty($errors) && !empty($externalIds)) {
            $post->update([
                'status' => 'published',
                'published_at' => now(),
                'external_ids' => $externalIds,
                'error_message' => null,
            ]);
            
            Log::info("Post {$post->id} published successfully to: " . implode(', ', array_keys($externalIds)));
        } elseif (!empty($externalIds)) {
            // Partial success
            $post->update([
                'status' => 'published',
                'published_at' => now(),
                'external_ids' => $externalIds,
                'error_message' => implode('; ', $errors),
            ]);
            
            Log::warning("Post {$post->id} partially published. Errors: " . implode('; ', $errors));
        } else {
            // Complete failure
            $post->update([
                'status' => 'failed',
                'error_message' => implode('; ', $errors),
                'retry_count' => $post->retry_count + 1,
            ]);
            
            Log::error("Post {$post->id} failed to publish: " . implode('; ', $errors));
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->post->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'retry_count' => $this->post->retry_count + 1,
        ]);

        Log::error("PublishToSocialMediaJob failed for post {$this->post->id}: " . $exception->getMessage());
    }
}
