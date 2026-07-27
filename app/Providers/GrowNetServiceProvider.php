<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\GrowNet\Contracts\ReferralProvider;
use App\Domain\GrowNet\Contracts\StarterKitProvider;
use App\Domain\GrowNet\Contracts\WalletProvider;
use App\Domain\GrowNet\MLM\Repositories\CommissionRepository as MLMCommissionRepository;
use App\Domain\GrowNet\MLM\Repositories\TeamVolumeRepository as MLMTeamVolumeRepository;
use App\Domain\GrowNet\Repositories\CommissionRepositoryInterface;
use App\Domain\GrowNet\Repositories\LoyaltyPointsRepositoryInterface;
use App\Domain\GrowNet\Repositories\MemberRepositoryInterface;
use App\Domain\GrowNet\Repositories\ReferralRepositoryInterface;
use App\Domain\GrowNet\Repositories\StarterKitRepositoryInterface;
use App\Domain\GrowNet\Repositories\TeamVolumeRepositoryInterface;
use App\Domain\GrowNet\Repositories\TierUpgradeRepositoryInterface;
use App\Domain\GrowNet\Reward\Repositories\AssetRepository;
use App\Domain\GrowNet\Reward\Repositories\PhysicalRewardAllocationRepository;
use App\Domain\GrowNet\Reward\Repositories\ReferralRepositoryInterface as RewardReferralRepositoryInterface;
use App\Domain\GrowNet\Services\DashboardService;
use App\Domain\GrowNet\Services\GrowNetBillingIntegration;
use App\Domain\GrowNet\Services\MemberService;
use App\Domain\GrowNet\Services\StarterKitService;
use App\Domain\GrowNet\Services\TierAdvancementService;
use App\Infrastructure\Contracts\GrowNet\ReferralProviderImpl;
use App\Infrastructure\Contracts\GrowNet\StarterKitProviderImpl;
use App\Infrastructure\Contracts\GrowNet\WalletProviderImpl;
use App\Infrastructure\Persistence\Eloquent\GrowNet\MemberModel;
use App\Infrastructure\Persistence\Repositories\EloquentAssetRepository;
use App\Infrastructure\Persistence\Repositories\EloquentCommissionRepository;
use App\Infrastructure\Persistence\Repositories\EloquentPhysicalRewardAllocationRepository;
use App\Infrastructure\Persistence\Repositories\EloquentReferralRepository;
use App\Infrastructure\Persistence\Repositories\EloquentTeamVolumeRepository;
use App\Infrastructure\Persistence\Repositories\GrowNet\EloquentCommissionRepository as GrowNetEloquentCommissionRepository;
use App\Infrastructure\Persistence\Repositories\GrowNet\EloquentLoyaltyPointsRepository;
use App\Infrastructure\Persistence\Repositories\GrowNet\EloquentMemberRepository;
use App\Infrastructure\Persistence\Repositories\GrowNet\EloquentReferralRepository as GrowNetEloquentReferralRepository;
use App\Infrastructure\Persistence\Repositories\GrowNet\EloquentStarterKitRepository;
use App\Infrastructure\Persistence\Repositories\GrowNet\EloquentTeamVolumeRepository as GrowNetEloquentTeamVolumeRepository;
use App\Infrastructure\Persistence\Repositories\GrowNet\EloquentTierUpgradeRepository;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\Core\Services\ModuleDiscovery;
use Illuminate\Support\ServiceProvider;

class GrowNetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(MemberRepositoryInterface::class, EloquentMemberRepository::class);
        $this->app->bind(CommissionRepositoryInterface::class, GrowNetEloquentCommissionRepository::class);
        $this->app->bind(ReferralRepositoryInterface::class, GrowNetEloquentReferralRepository::class);
        $this->app->bind(TeamVolumeRepositoryInterface::class, GrowNetEloquentTeamVolumeRepository::class);
        $this->app->bind(TierUpgradeRepositoryInterface::class, EloquentTierUpgradeRepository::class);
        $this->app->bind(StarterKitRepositoryInterface::class, EloquentStarterKitRepository::class);
        $this->app->bind(LoyaltyPointsRepositoryInterface::class, EloquentLoyaltyPointsRepository::class);

        // Bind migrated domain repository interfaces
        $this->app->bind(MLMCommissionRepository::class, EloquentCommissionRepository::class);
        $this->app->bind(MLMTeamVolumeRepository::class, EloquentTeamVolumeRepository::class);
        $this->app->bind(AssetRepository::class, EloquentAssetRepository::class);
        $this->app->bind(PhysicalRewardAllocationRepository::class, EloquentPhysicalRewardAllocationRepository::class);
        $this->app->bind(RewardReferralRepositoryInterface::class, EloquentReferralRepository::class);

        // Register domain services
        $this->app->singleton(DashboardService::class);
        $this->app->singleton(MemberService::class);
        $this->app->singleton(TierAdvancementService::class);
        $this->app->singleton(GrowNetBillingIntegration::class);

        // Register integration contracts
        $this->app->singleton(WalletProvider::class, WalletProviderImpl::class);
        $this->app->singleton(StarterKitProvider::class, StarterKitProviderImpl::class);
        $this->app->singleton(ReferralProvider::class, ReferralProviderImpl::class);

        // Register StarterKitService with explicit dependencies
        $this->app->singleton(StarterKitService::class, function ($app) {
            return new StarterKitService(
                $app->make(\App\Application\Notification\UseCases\SendNotificationUseCase::class),
                $app->make(\App\Domain\GrowNet\Wallet\Services\WalletService::class),
                $app->make(\App\Domain\GrowNet\Services\StarterKitBenefitService::class),
                $app->make(\App\Domain\Financial\Services\TransactionIntegrityService::class),
                $app->make(GrowNetBillingIntegration::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/grownet'));
        $this->loadMigrationsFrom(database_path('migrations/learning'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'grownet',
            name: 'GrowNet',
            version: '1.0.0',
            category: 'consumer',
            description: 'MLM network, memberships, tiers, commissions, rewards, loyalty, and learning',
            supportsSubdomain: false,
            capabilities: ['mlm', 'commissions', 'rewards', 'loyalty', 'tier_management', 'wallet', 'starter_kit', 'referral'],
            contracts: [
                \App\Domain\GrowNet\Contracts\WalletProvider::class,
                \App\Domain\GrowNet\Contracts\StarterKitProvider::class,
                \App\Domain\GrowNet\Contracts\ReferralProvider::class,
            ],
            permissions: ['manage_members', 'manage_commissions', 'manage_tiers', 'manage_rewards'],
            settings: ['commission_rate', 'tier_thresholds', 'reward_expiry_days'],
        ));
    }
}
