<?php

namespace App\Console\Commands\BizBoost;

use App\Domain\BizBoost\Services\SocialMedia\SocialMediaFactory;
use App\Infrastructure\Persistence\Eloquent\BizBoostIntegrationModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshSocialMediaTokensCommand extends Command
{
    protected $signature = 'bizboost:refresh-tokens';

    protected $description = 'Refresh expiring social media access tokens';

    public function handle(): int
    {
        $this->info('Checking for expiring tokens...');

        // Find tokens expiring in the next 7 days
        $expiringIntegrations = BizBoostIntegrationModel::where('status', 'active')
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<=', now()->addDays(7))
            ->get();

        if ($expiringIntegrations->isEmpty()) {
            $this->info('No expiring tokens found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$expiringIntegrations->count()} expiring token(s).");

        $refreshed = 0;
        $failed = 0;

        foreach ($expiringIntegrations as $integration) {
            try {
                $this->line("  → Refreshing {$integration->provider} token for business #{$integration->business_id}...");

                $service = SocialMediaFactory::make($integration->provider, $integration);
                $tokenData = $service->refreshToken();

                $integration->update([
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? $integration->refresh_token,
                    'token_expires_at' => isset($tokenData['expires_in'])
                        ? now()->addSeconds($tokenData['expires_in'])
                        : null,
                    'status' => 'active',
                ]);

                $this->info("    ✓ Token refreshed successfully");
                $refreshed++;

            } catch (\Exception $e) {
                $this->error("    ✗ Failed to refresh token: {$e->getMessage()}");

                $integration->update(['status' => 'expired']);

                Log::error("Failed to refresh {$integration->provider} token", [
                    'integration_id' => $integration->id,
                    'business_id' => $integration->business_id,
                    'error' => $e->getMessage(),
                ]);

                $failed++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Refreshed: {$refreshed}");
        $this->info("  Failed: {$failed}");

        return Command::SUCCESS;
    }
}
