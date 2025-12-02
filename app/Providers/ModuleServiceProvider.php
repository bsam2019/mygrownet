<?php

namespace App\Providers;

use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Repositories\ModuleUsageRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentModuleRepository;
use App\Infrastructure\Persistence\Repositories\EloquentModuleSubscriptionRepository;
use App\Infrastructure\Persistence\Repositories\EloquentModuleUsageRepository;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            ModuleRepositoryInterface::class,
            EloquentModuleRepository::class
        );
        
        $this->app->bind(
            ModuleSubscriptionRepositoryInterface::class,
            EloquentModuleSubscriptionRepository::class
        );

        $this->app->bind(
            ModuleUsageRepositoryInterface::class,
            EloquentModuleUsageRepository::class
        );

        // Register Application Layer services (Use Cases, Handlers)
        $this->registerUseCases();
        $this->registerCommandHandlers();
        $this->registerQueryHandlers();
    }

    /**
     * Register Use Cases
     */
    private function registerUseCases(): void
    {
        // Use Cases are auto-resolved by Laravel's container
        // No explicit binding needed unless you need singletons
    }

    /**
     * Register Command Handlers
     */
    private function registerCommandHandlers(): void
    {
        // Command Handlers are auto-resolved by Laravel's container
        // No explicit binding needed unless you need singletons
    }

    /**
     * Register Query Handlers
     */
    private function registerQueryHandlers(): void
    {
        // Query Handlers are auto-resolved by Laravel's container
        // No explicit binding needed unless you need singletons
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load module configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/modules.php',
            'modules'
        );
    }
}
