<?php

namespace App\Console\Commands\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostCampaignModel;
use App\Jobs\BizBoost\CampaignSequenceJob;
use Illuminate\Console\Command;

class ProcessCampaignsCommand extends Command
{
    protected $signature = 'bizboost:process-campaigns';

    protected $description = 'Process active BizBoost campaigns and schedule daily posts';

    public function handle(): int
    {
        $campaigns = BizBoostCampaignModel::where('status', 'active')
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No active campaigns to process.');
            return Command::SUCCESS;
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

        return Command::SUCCESS;
    }
}
          