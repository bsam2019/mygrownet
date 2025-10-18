<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Avoid registering heavy console commands unless explicitly enabled.
        // This prevents Artisan from resolving complex dependencies during bootstrap
        // (e.g., when running migrations), which has caused memory exhaustion.
        if ($this->app->runningInConsole() && (bool) env('REGISTER_APP_COMMANDS', false)) {
            $this->commands([
                AnnualProfitDistributionCommand::class,
                QuarterlyBonusDistributionCommand::class,
            ]);
        }
    }
}
