<?php

namespace App\Providers;

use App\Domain\GrowMart\Repositories\CartRepositoryInterface;
use App\Domain\GrowMart\Repositories\CategoryRepositoryInterface;
use App\Domain\GrowMart\Repositories\CouponRepositoryInterface;
use App\Domain\GrowMart\Repositories\OrderRepositoryInterface;
use App\Domain\GrowMart\Repositories\ProductRepositoryInterface;
use App\Domain\GrowMart\Repositories\ReviewRepositoryInterface;
use App\Domain\GrowMart\Repositories\WarehouseRepositoryInterface;
use App\Domain\GrowMart\Repositories\WishlistRepositoryInterface;
use App\Domain\GrowMart\Services\AnalyticsService;
use App\Domain\GrowMart\Services\CartService;
use App\Domain\GrowMart\Services\CouponService;
use App\Domain\GrowMart\Services\NotificationService;
use App\Domain\GrowMart\Services\OrderService;
use App\Domain\GrowMart\Services\PaymentService;
use App\Domain\GrowMart\Services\WishlistService;
use App\Infrastructure\Persistence\Repositories\GrowMart\EloquentCartRepository;
use App\Infrastructure\Persistence\Repositories\GrowMart\EloquentCategoryRepository;
use App\Infrastructure\Persistence\Repositories\GrowMart\EloquentCouponRepository;
use App\Infrastructure\Persistence\Repositories\GrowMart\EloquentOrderRepository;
use App\Infrastructure\Persistence\Repositories\GrowMart\EloquentProductRepository;
use App\Infrastructure\Persistence\Repositories\GrowMart\EloquentReviewRepository;
use App\Infrastructure\Persistence\Repositories\GrowMart\EloquentWarehouseRepository;
use App\Infrastructure\Persistence\Repositories\GrowMart\EloquentWishlistRepository;
use Illuminate\Support\ServiceProvider;

class GrowMartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, EloquentReviewRepository::class);
        $this->app->bind(CouponRepositoryInterface::class, EloquentCouponRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, EloquentWishlistRepository::class);
        $this->app->bind(WarehouseRepositoryInterface::class, EloquentWarehouseRepository::class);

        $this->app->singleton(CartService::class);
        $this->app->singleton(CouponService::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(WishlistService::class);
        $this->app->singleton(AnalyticsService::class);
        $this->app->singleton(NotificationService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/growmarket'));
    }
}
