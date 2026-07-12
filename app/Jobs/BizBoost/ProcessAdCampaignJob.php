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

class ProcessAdCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    private int $campaignId;

    public function __construct(int $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function handle(
        MetaAdsMarketingService $meta,
        WalletService $wallet,
    ): void {
        $campaign = BizBoostAdCampaignModel::find($this->campaignId);

        if (!$campaign || $campaign->status !== 'pending') {
            return;
        }

        try {
            $campaignResult = $meta->createCampaign([
                'name' => $campaign->name,
                'objective' => $campaign->objective ?? 'OUTCOME_TRAFFIC',
                'status' => 'PAUSED',
            ]);

            if (!$campaignResult['success']) {
                $campaign->update(['status' => 'failed', 'error_message' => $campaignResult['error'] ?? 'Meta API error']);
                $wallet->releaseLockedFunds($campaign->user_id, $campaign->client_budget, 'campaign_release_' . $campaign->id);
                return;
            }

            $campaign->update(['meta_campaign_id' => $campaignResult['id']]);

            $dailyBudget = round($campaign->meta_budget / max($campaign->duration_days, 1), 2);
            $targeting = $campaign->targeting ?? ['geo_locations' => ['countries' => ['ZM']], 'age_min' => 18, 'age_max' => 65];

            $adSetResult = $meta->createAdSet($campaignResult['id'], [
                'name' => $campaign->name . ' - Ad Set',
                'daily_budget' => $dailyBudget,
                'lifetime_budget' => $campaign->meta_budget,
                'start_time' => $campaign->start_date?->toIso8601String(),
                'end_time' => $campaign->end_date?->toIso8601String(),
                'targeting' => $targeting,
            ]);

            if ($adSetResult['success'] && isset($adSetResult['id'])) {
                $campaign->update(['meta_ad_set_id' => $adSetResult['id']]);
            }

            $creatives = $campaign->creatives;
            if ($creatives && $campaign->meta_ad_set_id) {
                $adResult = $meta->createAd($campaign->meta_ad_set_id, [
                    'name' => $campaign->name . ' - Ad',
                    'creative' => $creatives,
                ]);

                if ($adResult['success'] && isset($adResult['id'])) {
                    $campaign->update(['meta_ad_id' => $adResult['id'], 'status' => 'draft']);
                }
            }

            $campaign->update(['status' => 'draft']);

            Log::info("Ad campaign processed successfully", ['campaign_id' => $campaign->id, 'meta_id' => $campaignResult['id']]);
        } catch (\Exception $e) {
            Log::error("Ad campaign processing failed", ['campaign_id' => $campaign->id, 'error' => $e->getMessage()]);
            $campaign->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            if ($this->attempts() >= $this->tries) {
                $wallet->releaseLockedFunds($campaign->user_id, $campaign->client_budget, 'campaign_release_' . $campaign->id);
            }

            throw $e;
        }
    }
}
