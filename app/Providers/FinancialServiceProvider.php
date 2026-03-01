<?php

namespace App\Providers;

use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Wallet\Repositories\WalletRepositoryInterface;
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
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
