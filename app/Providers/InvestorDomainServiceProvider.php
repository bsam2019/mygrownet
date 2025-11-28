<?php

namespace App\Providers;

use App\Domain\Investor\Repositories\ShareCertificateRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\EloquentShareCertificateRepository;
use Illuminate\Support\ServiceProvider;

class InvestorDomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            ShareCertificateRepositoryInterface::class,
            EloquentShareCertificateRepository::class
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
