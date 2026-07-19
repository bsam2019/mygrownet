<?php

namespace App\Providers;

use App\Domain\BMS\BizDocs\Adapters\BizDocsAdapter;
use App\Domain\BMS\BizDocs\Contracts\DocumentGeneratorInterface;
use Illuminate\Support\ServiceProvider;

class BizDocsIntegrationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind the DocumentGeneratorInterface to BizDocsAdapter
        $this->app->bind(DocumentGeneratorInterface::class, BizDocsAdapter::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
