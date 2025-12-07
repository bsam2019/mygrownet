<?php

namespace App\Console\Commands\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostCampaignModel;
use App\Jobs\BizBoost\CampaignSequenceJob;
use Illuminate\Console\Command;

class ProcessCampaignSequencesCommand extends Command
{
    protected $signature = 'bizboost:process-campaigns';
    protected $description = 'Process active campaign sequences and schedule posts';

    public function handle(): int
    {
        $this->info('Processing active BizBoost campaigns...');

        $campaigns = BizBoostCampaignModel::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No active campaigns to process.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($campaigns as $campaign) {
            CampaignSequenceJob::dispatch($campaign->id);
            $count++;
        }

        $this->info("Dispatched {$count} campaign sequence jobs.");

        return Command::SUCCESS;
    }
}
