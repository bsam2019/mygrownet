<?php

namespace App\Jobs\BizBoost;

use App\Domain\BizBoost\Services\SocialMedia\SocialMediaFactory;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishPostToSocialMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    public function __construct(
        public BizBoostPostModel $post,
        public string $platform
    ) {}

    public function handle(): void
    {
        try {
            // Get integration for this platform
            $integration = BizBoostIntegrationModel::where('business_id', $this->post->business_id)
                ->where('provider', $this->platform)
                ->where('status', 'active')
                ->first();

            if (!$integration) {
                throw new \Exception("No active {$this->platform} integration found");
            }

            // Validate token before publishing
            $service = SocialMediaFactory::make($this->platform, $integration);

            if (!$service->validateToken()) {
                // Try to refresh token
                try {
                    $tokenData = $service->refreshToken();
                    $integration->update([
                        'access_token' => $tokenData['access_token'],
                        'token_expires_at' => isset($tokenData['expires_in'])
                            ? now()->addSeconds($tokenData['expires_in'])
                            : null,
                    ]);
                } catch (\Exception $e) {
                    $integration->update(['status' => 'expired']);
                    throw new \Exception("Token expired and refresh failed: {$e->getMessage()}");
                }
            }

            // Publish the post
            $result = $service->publishPost($this->post);

            // Update post with external ID
            $externalIds = $this->post->external_ids ?? [];
            $externalIds[$this->platform] = $result['id'] ?? $result['post_id'] ?? null;

            $this->post->update([
                'status' => 'published',
                'published_at' => now(),
                'external_ids' => $externalIds,
                'error_message' => null,
            ]);

            // Update integration last used
            $integration->update(['last_used_at' => now()]);

            Log::info("Post published to {$this->platform}", [
                'post_id' => $this->post->id,
                'external_id' => $externalIds[$this->platform],
            ]);

        } catch (\Exception $e) {
            $this->post->increment('retry_count');

            $this->post->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Failed to publish post to {$this->platform}", [
                'post_id' => $this->post->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Re-throw to trigger retry
            if ($this->attempts() < $this->tries) {
                throw $e;
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Post publishing permanently failed", [
            'post_id' => $this->post->id,
            'platform' => $this->platform,
            'error' => $exception->getMessage(),
        ]);

        $this->post->update([
            'status' => 'failed',
            'error_message' => "Permanently failed after {$this->tries} attempts: {$exception->getMessage()}",
        ]);
    }
}
