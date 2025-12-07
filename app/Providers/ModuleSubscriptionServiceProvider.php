<?php

namespace App\Providers;

use App\Domain\BizBoost\Services\BizBoostUsageProvider;
use App\Domain\GrowBiz\Services\GrowBizUsageProvider;
use App\Domain\GrowFinance\Services\GrowFinanceUsageProvider;
use App\Domain\Module\Contracts\ModuleUsageProviderInterface;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Services\ModuleAccessService;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Domain\Module\Services\UsageLimitService;
use App\Infrastructure\Persistence\Repositories\ConfigBasedModuleRepository;
use App\Infrastructure\Persistence\Repositories\EloquentModuleSubscriptionRepository;
use Illuminate\Support\ServiceProvider;

class ModuleSubscriptionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->singleton(ModuleRepositoryInterface::class, ConfigBasedModuleRepository::class);
        $this->app->singleton(ModuleSubscriptionRepositoryInterface::class, EloquentModuleSubscriptionRepository::class);

        // Register TierConfigurationService as singleton
        $this->app->singleton(TierConfigurationService::class);

        // Register UsageLimitService as singleton
        $this->app->singleton(UsageLimitService::class);

        // Register ModuleAccessService as singleton
        $this->app->singleton(ModuleAccessService::class);

        // Register unified SubscriptionService as singleton
        $this->app->singleton(SubscriptionService::class, function ($app) {
            return new SubscriptionService(
                $app->make(UsageLimitService::class),
                $app->make(TierConfigurationService::class),
                $app->make(ModuleAccessService::class)
            );
        });

        // Register usage providers
        $this->app->singleton(GrowFinanceUsageProvider::class);
        $this->app->singleton(GrowBizUsageProvider::class);
        $this->app->singleton(BizBoostUsageProvider::class);

        // Tag all usage providers for easy collection
        $this->app->tag([
            GrowFinanceUsageProvider::class,
            GrowBizUsageProvider::class,
            BizBoostUsageProvider::class,
        ], 'module.usage.providers');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register all usage providers with the UsageLimitService
        $usageLimitService = $this->app->make(UsageLimitService::class);
        
        foreach ($this->app->tagged('module.usage.providers') as $provider) {
            $usageLimitService->registerProvider($provider);
        }

        // Merge module configurations
        $this->mergeModuleConfigs();
    }

    /**
     * Merge module configuration files
     */
    private function mergeModuleConfigs(): void
    {
        $modulesPath = config_path('modules');
        
        if (!is_dir($modulesPath)) {
            return;
        }

        foreach (glob("{$modulesPath}/*.php") as $configFile) {
            $moduleId = basename($configFile, '.php');
            $this->mergeConfigFrom($configFile, "modules.{$moduleId}");
        }
    }
}
