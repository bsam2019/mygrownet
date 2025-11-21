<?php

namespace App\Providers;

use App\Domain\EmailMarketing\Repositories\EmailCampaignRepository;
use App\Infrastructure\Persistence\Repositories\EloquentEmailCampaignRepository;
use Illuminate\Support\ServiceProvider;

class EmailMarketingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(
            EmailCampaignRepository::class,
            EloquentEmailCampaignRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
