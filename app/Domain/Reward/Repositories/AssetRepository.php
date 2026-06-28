<?php

declare(strict_types=1);

namespace App\Domain\Reward\Repositories;

use App\Domain\Reward\ValueObjects\RewardId;
use App\Domain\Reward\ValueObjects\AssetType;
use App\Domain\Reward\ValueObjects\AssetValue;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\TeamVolumeAmount;
use DateTimeImmutable;

interface AssetRepository
{
    /**
     * Find asset by ID
     */
    public function findById(RewardId $id): ?array;

    /**
     * Find assets by type
     */
    public function findByType(AssetType $type): array;

    /**
     * Find available assets for allocation
     */
    public function findAvailableAssets(): array;

    /**
     * Find assets available for specific tier
     */
    public function findAvailableForTier(string $tierName): array;

    /**
     * Check asset availability
     */
    public function isAssetAvailable(RewardId $assetId): bool;

    /**
     * Check if user is eligible for specific asset
     */
    public function checkUserEligibility(
        UserId $userId, 
        RewardId $assetId,
        TeamVolumeAmount $teamVolume,
        int $activeReferrals,
        int $consecutiveMonths
    ): bool;

    /**
     * Get assets eligible for user based on current performance
     */
    public function getEligibleAssetsForUser(
        UserId $userId,
        string $tierName,
        TeamVolumeAmount $teamVolume,
        int $activeReferrals,
        int $consecutiveMonths
    ): array;

    /**
     * Reserve asset for allocation
     */
    public function reserveAsset(RewardId $assetId): bool;

    /**
     * Release asset reservation
     */
    public function releaseAssetReservation(RewardId $assetId): bool;

    /**
     * Update asset allocation count
     */
    public function updateAllocationCount(RewardId $assetId, int $change): void;

    /**
     * Get asset allocation statistics
     */
    public function getAssetAllocationStats(): array;

    /**
     * Get assets by value range
     */
    public function findByValueRange(AssetValue $minValue, AssetValue $maxValue): array;

    /**
     * Get income-generating assets
     */
    public function findIncomeGeneratingAssets(): array;

    /**
     * Get assets requiring maintenance
     */
    public function findAssetsRequiringMaintenance(): array;

    /**
     * Get asset inventory report
     */
    public function getInventoryReport(): array;

    /**
     * Get asset performance metrics
     */
    public function getAssetPerformanceMetrics(RewardId $assetId): array;

    /**
     * Find assets by category
     */
    public function findByCategory(string $category): array;

    /**
     * Get low stock alerts
     */
    public function getLowStockAlerts(int $threshold = 5): array;

    /**
     * Update asset specifications
     */
    public function updateAssetSpecifications(RewardId $assetId, array $specifications): void;

    /**
     * Deactivate asset
     */
    public function deactivateAsset(RewardId $assetId): void;

    /**
     * Reactivate asset
     */
    public function reactivateAsset(RewardId $assetId): void;
}