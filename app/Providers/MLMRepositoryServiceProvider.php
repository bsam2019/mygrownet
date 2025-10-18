<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\MLM\Repositories\CommissionRepository;
use App\Domain\MLM\Repositories\TeamVolumeRepository;
use App\Infrastructure\Persistence\Repositories\EloquentCommissionRepository;
use App\Infrastructure\Persistence\Repositories\EloquentTeamVolumeRepository;

class MLMRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // MLM Repositories
        $this->app->bind(CommissionRepository::class, EloquentCommissionRepository::class);
        $this->app->bind(TeamVolumeRepository::class, EloquentTeamVolumeRepository::class);
        
        // Asset Repositories
        $this->app->bind(
            \App\Domain\Reward\Repositories\AssetRepository::class,
            \App\Infrastructure\Persistence\Repositories\EloquentAssetRepository::class
        );
        $this->app->bind(
            \App\Domain\Reward\Repositories\PhysicalRewardAllocationRepository::class,
            \App\Infrastructure\Persistence\Repositories\EloquentPhysicalRewardAllocationRepository::class
        );
        
        // Community Repositories
        $this->app->bind(
            \App\Domain\Community\Repositories\ProjectRepository::class,
            \App\Infrastructure\Persistence\Repositories\EloquentProjectRepository::class
        );
        $this->app->bind(
            \App\Domain\Community\Repositories\ContributionRepository::class,
            \App\Infrastructure\Persistence\Repositories\EloquentContributionRepository::class
        );
        $this->app->bind(
            \App\Domain\Community\Repositories\VotingRepository::class,
            \App\Infrastructure\Persistence\Repositories\EloquentVotingRepository::class
        );
        $this->app->bind(
            \App\Domain\Community\Repositories\ProfitDistributionRepository::class,
            \App\Infrastructure\Persistence\Repositories\EloquentProfitDistributionRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}