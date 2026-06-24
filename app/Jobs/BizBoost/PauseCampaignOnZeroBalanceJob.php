<?php

namespace App\Jobs\BizBoost;

use App\Domain\BizBoost\Services\WalletService;
use App\Infrastructure\Persistence\Eloquent\BizBoostAdCampaignModel;
use App\Infrastructure\Services\MetaAdsMarketingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PauseCampaignOnZeroBalanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(
        WalletService $wallet,
        MetaAdsMarketingService $meta,
    ): void {
        $activeCampaigns = BizBoostAdCampaignModel::where('status', 'active')
            ->whereNotNull('meta_campaign_id')
            ->get();

        $paused = 0;

        foreach ($activeCampaigns as $campaign) {
            $available = $wallet->getAvailableBalance($campaign->user_id);

            if ($available <= 0) {
                try {
                    $success = $meta->pauseCampaign($campaign->meta_campaign_id);

                    if ($success) {
                        $campaign->update([
                            'status' => 'paused',
                            'error_message' => 'Paused automatically: wallet balance depleted',
                        ]);
                        $paused++;
                        Log::info("Campaign auto-paused due to zero balance", [
                            'campaign_id' => $campaign->id,
                            'user_id' => $campaign->user_id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to auto-pause campaign", [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        Log::info("PauseCampaignOnZeroBalanceJob completed", ['checked' => $activeCampaigns->count(), 'paused' => $paused]);
    }
}
