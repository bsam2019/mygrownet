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
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\Core\Services\ModuleDiscovery;
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

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'marketplace',
            name: 'Marketplace',
            version: '1.0.0',
            category: 'business',
            description: 'Multi-vendor marketplace with sellers, products, orders, payouts, and escrow',
            supportsSubdomain: true,
            capabilities: ['marketplace', 'multi_vendor', 'escrow', 'payouts'],
            permissions: ['manage_sellers', 'manage_products', 'manage_orders', 'manage_payouts'],
        ));
    }
}
