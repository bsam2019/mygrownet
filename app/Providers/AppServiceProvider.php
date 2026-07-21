<?php

namespace App\Providers;

use App\Console\Commands\MergeDuplicateUsers;
use App\Console\Commands\StockFlowCheckGuardUsage;
use App\Domain\Core\Contracts\MyGrowIdentity;
use App\Domain\Core\Services\MyGrowIdentityService;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\AnnualProfitDistributionCommand;
use App\Console\Commands\QuarterlyBonusDistributionCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MyGrowIdentity::class, MyGrowIdentityService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Avoid registering heavy console commands unless explicitly enabled.
        // This prevents Artisan from resolving complex dependencies during bootstrap
        // (e.g., when running migrations), which has caused memory exhaustion.
        $this->commands([
            StockFlowCheckGuardUsage::class,
            MergeDuplicateUsers::class,
        ]);

        if ($this->app->runningInConsole() && (bool) env('REGISTER_APP_COMMANDS', false)) {
            $this->commands([
                AnnualProfitDistributionCommand::class,
                QuarterlyBonusDistributionCommand::class,
            ]);
        }
    }
}
