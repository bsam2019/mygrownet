<?php

namespace App\Domain\BizBoost\Contracts;

interface AdsMarketingServiceInterface
{
    public function createCampaign(array $params): array;
    public function createAdSet(string $campaignId, array $params): array;
    public function createAd(string $adSetId, array $params): array;
    public function updateCampaign(string $campaignId, array $params): array;
    public function getCampaign(string $campaignId): array;
    public function pauseCampaign(string $campaignId): bool;
    public function resumeCampaign(string $campaignId): bool;
    public function deleteCampaign(string $campaignId): bool;
    public function getInsights(string $campaignId, array $params = []): array;
    public function setDailyBudget(string $campaignId, float $budget): bool;
    public function setLifetimeBudget(string $campaignId, float $budget): bool;
}
