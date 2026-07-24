<?php

namespace App\Providers;

use App\Domain\Investment\Repositories\InvestmentCategoryRepositoryInterface;
use App\Domain\Investment\Repositories\InvestmentRepositoryInterface;
use App\Domain\Investment\Repositories\InvestmentTierRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\Investment\EloquentInvestmentCategoryRepository;
use App\Infrastructure\Persistence\Repositories\Investment\EloquentInvestmentRepository;
use App\Infrastructure\Persistence\Repositories\Investment\EloquentInvestmentTierRepository;
use Illuminate\Support\ServiceProvider;

class InvestmentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InvestmentRepositoryInterface::class, EloquentInvestmentRepository::class);
        $this->app->bind(InvestmentCategoryRepositoryInterface::class, EloquentInvestmentCategoryRepository::class);
        $this->app->bind(InvestmentTierRepositoryInterface::class, EloquentInvestmentTierRepository::class);
    }
}
