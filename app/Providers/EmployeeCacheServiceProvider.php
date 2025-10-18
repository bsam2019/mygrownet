<?php

declare(strict_types=1);

namespace App\Providers;

use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Infrastructure\Persistence\Repositories\CachedEmployeeRepository;
use App\Infrastructure\Persistence\Repositories\EloquentEmployeeRepository;
use App\Infrastructure\Persistence\Repositories\OptimizedEmployeeRepository;
use App\Listeners\Employee\InvalidateEmployeeCacheListener;
use App\Services\EmployeeCacheService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Employee Cache Service Provider
 * 
 * Registers caching services and event listeners for the employee management system.
 * Configures the repository pattern with caching decorators and cache invalidation strategies.
 */
class EmployeeCacheServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register the optimized repository
        $this->app->singleton(OptimizedEmployeeRepository::class, function ($app) {
            return new OptimizedEmployeeRepository(new EmployeeModel());
        });

        // Register the cache service
        $this->app->singleton(EmployeeCacheService::class, function ($app) {
            return new EmployeeCacheService(
                $app->make(OptimizedEmployeeRepository::class)
            );
        });

        // Register the cached repository decorator
        $this->app->singleton(CachedEmployeeRepository::class, function ($app) {
            return new CachedEmployeeRepository(
                $app->make(OptimizedEmployeeRepository::class),
                $app->make(EmployeeCacheService::class)
            );
        });

        // Bind the interface to the cached repository when caching is enabled
        if (config('cache.employee.enabled', true)) {
            $this->app->bind(
                \App\Domain\Employee\Repositories\EmployeeRepositoryInterface::class,
                CachedEmployeeRepository::class
            );
        } else {
            // Fall back to the standard repository if caching is disabled
            $this->app->bind(
                \App\Domain\Employee\Repositories\EmployeeRepositoryInterface::class,
                EloquentEmployeeRepository::class
            );
        }
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Register cache invalidation event listeners
        Event::subscribe(InvalidateEmployeeCacheListener::class);

        // Register console commands for cache management
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\Employee\WarmEmployeeCacheCommand::class,
                \App\Console\Commands\Employee\ClearEmployeeCacheCommand::class,
            ]);
        }

        // Publish configuration if needed
        $this->publishes([
            __DIR__.'/../../config/employee-cache.php' => config_path('employee-cache.php'),
        ], 'employee-cache-config');
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [
            OptimizedEmployeeRepository::class,
            EmployeeCacheService::class,
            CachedEmployeeRepository::class,
            \App\Domain\Employee\Repositories\EmployeeRepositoryInterface::class,
        ];
    }
}