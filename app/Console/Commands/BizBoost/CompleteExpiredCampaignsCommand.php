<?php

namespace App\Console\Commands\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostCampaignModel;
use Illuminate\Console\Command;

class CompleteExpiredCampaignsCommand extends Command
{
    protected $signature = 'bizboost:complete-expired-campaigns';

    protected $description = 'Mark expired BizBoost campaigns as completed';

    public function handle(): int
    {
        // Find active campaigns that have passed their end date
        $expiredCampaigns = BizBoostCampaignModel::where('status', 'active')
            ->where('end_date', '<', now()->toDateString())
            ->get();

        if ($expiredCampaigns->isEmpty()) {
            $this->info('No expired campaigns to complete.');
            return Command::SUCCESS;
        }

        $this->info("Found {$expiredCampaigns->count()} expired campaign(s) to complete.");

        foreach ($expiredCampaigns as $campaign) {
            try {
                $campaign->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                $this->line("  ✓ Completed campaign #{$campaign->id}: {$campaign->name}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to complete campaign #{$campaign->id}: {$e->getMessage()}");
            }
        }

        $this->info('Done completing expired campaigns.');

        return Command::SUCCESS;
    }
}
