<?php

namespace App\Providers;

use App\Domain\Marketplace\Repositories\SellerRepositoryInterface;
use App\Domain\Marketplace\Repositories\ProductRepositoryInterface;
use App\Domain\Marketplace\Repositories\OrderRepositoryInterface;
use App\Domain\Marketplace\Repositories\CategoryRepositoryInterface;
use App\Domain\Marketplace\Repositories\PayoutRepositoryInterface;
use App\Domain\Marketplace\Repositories\EscrowRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentSellerRepository;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentProductRepository;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentOrderRepository;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentPayoutRepository;
use App\Infrastructure\Persistence\Repositories\Marketplace\EloquentEscrowRepository;
use Illuminate\Support\ServiceProvider;

class MarketplaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SellerRepositoryInterface::class, EloquentSellerRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(PayoutRepositoryInterface::class, EloquentPayoutRepository::class);
        $this->app->bind(EscrowRepositoryInterface::class, EloquentEscrowRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/marketplace'));
    }
}
