<?php

namespace App\Infrastructure\Services;

use App\Domain\BizBoost\Contracts\AdsMarketingServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaAdsMarketingService implements AdsMarketingServiceInterface
{
    private string $baseUrl;
    private string $apiVersion;
    private string $accessToken;
    private ?string $adAccountId;

    public function __construct()
    {
        $this->baseUrl = 'https://graph.facebook.com';
        $this->apiVersion = config('services.meta.marketing_api_version', 'v21.0');
        $this->accessToken = config('services.meta.system_user_token', '');
        $this->adAccountId = config('services.meta.ad_account_id', '');
    }

    public function createCampaign(array $params): array
    {
        $accountId = $this->resolveAdAccountId($params['ad_account_id'] ?? null);
        $response = Http::post("{$this->baseUrl}/{$this->apiVersion}/act_{$accountId}/campaigns", [
            'name' => $params['name'],
            'objective' => $params['objective'] ?? 'OUTCOME_TRAFFIC',
            'status' => $params['status'] ?? 'PAUSED',
            'special_ad_categories' => $params['special_ad_categories'] ?? [],
            'access_token' => $this->accessToken,
        ]);

        return $this->handleResponse($response, 'campaign');
    }

    public function createAdSet(string $campaignId, array $params): array
    {
        $accountId = $this->resolveAdAccountId($params['ad_account_id'] ?? null);
        $response = Http::post("{$this->baseUrl}/{$this->apiVersion}/act_{$accountId}/adsets", [
            'name' => $params['name'],
            'campaign_id' => $campaignId,
            'daily_budget' => $params['daily_budget'] ?? null,
            'lifetime_budget' => $params['lifetime_budget'] ?? null,
            'start_time' => $params['start_time'] ?? now()->addHour()->toIso8601String(),
            'end_time' => $params['end_time'] ?? null,
            'bid_strategy' => $params['bid_strategy'] ?? 'LOWEST_COST_WITHOUT_CAP',
            'targeting' => $params['targeting'] ?? $this->defaultTargeting(),
            'status' => $params['status'] ?? 'PAUSED',
            'optimization_goal' => $params['optimization_goal'] ?? 'REACH',
            'billing_event' => $params['billing_event'] ?? 'IMPRESSIONS',
            'access_token' => $this->accessToken,
        ]);

        return $this->handleResponse($response, 'ad_set');
    }

    public function createAd(string $adSetId, array $params): array
    {
        $accountId = $this->resolveAdAccountId($params['ad_account_id'] ?? null);
        $response = Http::post("{$this->baseUrl}/{$this->apiVersion}/act_{$accountId}/ads", [
            'name' => $params['name'],
            'adset_id' => $adSetId,
            'creative' => $params['creative'],
            'status' => $params['status'] ?? 'PAUSED',
            'access_token' => $this->accessToken,
        ]);

        return $this->handleResponse($response, 'ad');
    }

    public function updateCampaign(string $campaignId, array $params): array
    {
        $response = Http::post("{$this->baseUrl}/{$this->apiVersion}/{$campaignId}", array_merge(
            $params,
            ['access_token' => $this->accessToken]
        ));

        return $this->handleResponse($response, 'campaign_update');
    }

    public function getCampaign(string $campaignId): array
    {
        $response = Http::get("{$this->baseUrl}/{$this->apiVersion}/{$campaignId}", [
            'fields' => 'id,name,objective,status,daily_budget,lifetime_budget,start_time,end_time,insights',
            'access_token' => $this->accessToken,
        ]);

        return $this->handleResponse($response, 'campaign_get');
    }

    public function pauseCampaign(string $campaignId): bool
    {
        return $this->setCampaignStatus($campaignId, 'PAUSED');
    }

    public function resumeCampaign(string $campaignId): bool
    {
        return $this->setCampaignStatus($campaignId, 'ACTIVE');
    }

    public function deleteCampaign(string $campaignId): bool
    {
        try {
            $response = Http::delete("{$this->baseUrl}/{$this->apiVersion}/{$campaignId}", [
                'access_token' => $this->accessToken,
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Meta delete campaign failed", ['campaign_id' => $campaignId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function getInsights(string $campaignId, array $params = []): array
    {
        $fields = $params['fields'] ?? 'impressions,clicks,spend,reach,ctr,cpc,cpm,frequency,actions';
        $response = Http::get("{$this->baseUrl}/{$this->apiVersion}/{$campaignId}/insights", [
            'fields' => $fields,
            'date_preset' => $params['date_preset'] ?? 'last_7d',
            'level' => $params['level'] ?? 'campaign',
            'access_token' => $this->accessToken,
        ]);

        return $this->handleResponse($response, 'insights');
    }

    public function setDailyBudget(string $campaignId, float $budget): bool
    {
        return $this->updateCampaignField($campaignId, ['daily_budget' => (int) ($budget * 100)]);
    }

    public function setLifetimeBudget(string $campaignId, float $budget): bool
    {
        return $this->updateCampaignField($campaignId, ['lifetime_budget' => (int) ($budget * 100)]);
    }

    private function setCampaignStatus(string $campaignId, string $status): bool
    {
        return $this->updateCampaignField($campaignId, ['status' => $status]);
    }

    private function updateCampaignField(string $campaignId, array $fields): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/{$this->apiVersion}/{$campaignId}", array_merge(
                $fields,
                ['access_token' => $this->accessToken]
            ));
            return $response->successful() && !isset($response->json()['error']);
        } catch (\Exception $e) {
            Log::error("Meta update campaign field failed", ['campaign_id' => $campaignId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    private function handleResponse($response, string $context): array
    {
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['error'])) {
                Log::error("Meta API error [{$context}]", ['error' => $data['error']]);
                return ['success' => false, 'error' => $data['error']['message'] ?? 'Unknown error'];
            }
            return ['success' => true, 'data' => $data, 'id' => $data['id'] ?? null];
        }

        $error = $response->json('error.message') ?? $response->body();
        Log::error("Meta API HTTP error [{$context}]", ['status' => $response->status(), 'error' => $error]);
        return ['success' => false, 'error' => $error];
    }

    private function resolveAdAccountId(?string $accountId): string
    {
        return $accountId ?? $this->adAccountId;
    }

    private function defaultTargeting(): array
    {
        return [
            'geo_locations' => [
                'countries' => ['ZM'],
            ],
            'age_min' => 18,
            'age_max' => 65,
        ];
    }
}
