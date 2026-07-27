<?php

namespace App\Providers;

use App\Domain\Core\Services\DimensionResolver;
use App\Domain\Core\Services\EventOwnershipRegistry;
use App\Domain\Core\Services\ModuleDiscovery;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\FinancialServicesCore\Contracts\CurrencyService;
use App\Domain\FinancialServicesCore\Contracts\ExchangeRateProvider;
use App\Domain\FinancialServicesCore\Events\FxRateUpdated;
use App\Domain\FinancialServicesCore\Infrastructure\EloquentCurrencyRepository;
use App\Domain\FinancialServicesCore\Infrastructure\EloquentExchangeRateRepository;
use App\Domain\FinancialServicesCore\Infrastructure\FxDimensionProvider;
use App\Domain\FinancialServicesCore\Repositories\CurrencyRepositoryInterface;
use App\Domain\FinancialServicesCore\Repositories\ExchangeRateRepositoryInterface;
use App\Domain\FinancialServicesCore\Services\CurrencyServiceImpl;
use App\Domain\FinancialServicesCore\Services\ExchangeRateProviderImpl;
use Illuminate\Support\ServiceProvider;

class FinancialServicesCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CurrencyService::class, CurrencyServiceImpl::class);
        $this->app->bind(ExchangeRateProvider::class, ExchangeRateProviderImpl::class);

        $this->app->bind(CurrencyRepositoryInterface::class, EloquentCurrencyRepository::class);
        $this->app->bind(ExchangeRateRepositoryInterface::class, EloquentExchangeRateRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/financial-services-core'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'financial-services-core',
            name: 'Financial Services Core',
            version: '1.0.0',
            category: 'platform',
            type: 'platform',
            description: 'Shared currency management, exchange rates, and FX conversion',
            requiresOrganization: false,
            capabilities: ['currency_conversion', 'exchange_rate_fetching'],
            contracts: [CurrencyService::class, ExchangeRateProvider::class],
            permissions: ['manage_currencies', 'manage_exchange_rates', 'view_rates'],
            settings: ['base_currency', 'rate_fetch_schedule', 'fallback_rate_source', 'exchange_rate_api_key'],
            events: [FxRateUpdated::class],
            healthChecks: ['database'],
        ));

        $registry = $this->app->make(EventOwnershipRegistry::class);
        $registry->register(FxRateUpdated::NAME, 'financial-services-core');

        $this->app->make(DimensionResolver::class)->register(
            $this->app->make(FxDimensionProvider::class)
        );
    }
}
