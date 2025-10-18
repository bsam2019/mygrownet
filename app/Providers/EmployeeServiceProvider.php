<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Employee\Repositories\EmployeeRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentEmployeeRepository;
use App\Infrastructure\Persistence\Repositories\CachedEmployeeRepository;
use App\Infrastructure\Persistence\Repositories\OptimizedEmployeeRepository;
use App\Domain\Employee\Services\EmployeeRegistrationService;
use App\Domain\Employee\Services\PerformanceTrackingService;
use App\Domain\Employee\Services\CommissionCalculationService;
use App\Domain\Employee\Services\PayrollCalculationService;
use App\Services\EmployeeCacheService;
use App\Services\EmployeeRoleIntegrationService;

class EmployeeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(EmployeeRepositoryInterface::class, function ($app) {
            $baseRepository = new EloquentEmployeeRepository($app->make(\App\Infrastructure\Persistence\Eloquent\EmployeeModel::class));
            $optimizedRepository = new OptimizedEmployeeRepository($baseRepository);
            return new CachedEmployeeRepository($optimizedRepository, $app->make(EmployeeCacheService::class));
        });

        // Domain service bindings
        $this->app->singleton(EmployeeRegistrationService::class);
        $this->app->singleton(PerformanceTrackingService::class);
        $this->app->singleton(CommissionCalculationService::class);
        $this->app->singleton(PayrollCalculationService::class);

        // Application service bindings
        $this->app->singleton(EmployeeCacheService::class);
        $this->app->singleton(EmployeeRoleIntegrationService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register employee management console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\Employee\ClearEmployeeCacheCommand::class,
                \App\Console\Commands\Employee\WarmEmployeeCacheCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            EmployeeRepositoryInterface::class,
            EmployeeRegistrationService::class,
            PerformanceTrackingService::class,
            CommissionCalculationService::class,
            PayrollCalculationService::class,
            EmployeeCacheService::class,
            EmployeeRoleIntegrationService::class,
        ];
    }
}