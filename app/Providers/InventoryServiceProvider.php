<?php

namespace App\Providers;

use App\Domain\Inventory\Repositories\InventoryAlertRepositoryInterface;
use App\Domain\Inventory\Repositories\InventoryCategoryRepositoryInterface;
use App\Domain\Inventory\Repositories\InventoryItemRepositoryInterface;
use App\Domain\Inventory\Repositories\StockMovementRepositoryInterface;
use App\Domain\Inventory\Services\InventoryService;
use App\Infrastructure\Persistence\Repositories\Inventory\EloquentInventoryAlertRepository;
use App\Infrastructure\Persistence\Repositories\Inventory\EloquentInventoryCategoryRepository;
use App\Infrastructure\Persistence\Repositories\Inventory\EloquentInventoryItemRepository;
use App\Infrastructure\Persistence\Repositories\Inventory\EloquentStockMovementRepository;
use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InventoryCategoryRepositoryInterface::class, EloquentInventoryCategoryRepository::class);
        $this->app->bind(InventoryItemRepositoryInterface::class, EloquentInventoryItemRepository::class);
        $this->app->bind(StockMovementRepositoryInterface::class, EloquentStockMovementRepository::class);
        $this->app->bind(InventoryAlertRepositoryInterface::class, EloquentInventoryAlertRepository::class);

        $this->app->singleton(InventoryService::class, function ($app) {
            return new InventoryService(
                $app->make(InventoryCategoryRepositoryInterface::class),
                $app->make(InventoryItemRepositoryInterface::class),
                $app->make(StockMovementRepositoryInterface::class),
                $app->make(InventoryAlertRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/inventory'));
    }
}
