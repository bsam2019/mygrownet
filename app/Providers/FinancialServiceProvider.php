<?php

namespace App\Providers;

use App\Domain\Core\Services\ModuleDiscovery;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\Financial\Contracts\LoanProvider;
use App\Domain\Financial\Services\LoanService;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
use App\Infrastructure\Contracts\Financial\LoanProviderImpl;
use App\Infrastructure\Persistence\Eloquent\EloquentTransactionRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentWalletRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Financial Service Provider
 * 
 * Registers financial domain services and repositories.
 * Binds interfaces to concrete implementations.
 */
class FinancialServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to Eloquent implementations
        $this->app->bind(
            WalletRepositoryInterface::class,
            EloquentWalletRepository::class
        );

        $this->app->bind(
            TransactionRepositoryInterface::class,
            EloquentTransactionRepository::class
        );

        // Register LoanService and LoanProvider contract
        $this->app->singleton(LoanService::class);
        $this->app->singleton(LoanProvider::class, LoanProviderImpl::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'financial',
            name: 'Financial',
            version: '1.0.0',
            category: 'business',
            description: 'Financial domain (loans, transactions, wallets)',
            supportsSubdomain: false,
            capabilities: ['loans', 'transactions', 'wallets'],
            contracts: [
                \App\Domain\Financial\Contracts\LoanProvider::class,
            ],
            permissions: ['issue_loans', 'manage_wallets', 'view_transactions'],
        ));
    }
}
