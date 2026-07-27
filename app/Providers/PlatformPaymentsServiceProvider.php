<?php

namespace App\Providers;

use App\Domain\Core\Services\DimensionResolver;
use App\Domain\Core\Services\EventOwnershipRegistry;
use App\Domain\Core\Services\IntegrationRegistry;
use App\Domain\Core\Services\ModuleDiscovery;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\PlatformPayments\Contracts\PaymentGateway;
use App\Domain\PlatformPayments\Contracts\SettlementProvider;
use App\Domain\PlatformPayments\Events\PaymentAttemptFailed;
use App\Domain\PlatformPayments\Events\PaymentCompleted;
use App\Domain\PlatformPayments\Events\PaymentFailed;
use App\Domain\PlatformPayments\Events\PaymentInitiated;
use App\Domain\PlatformPayments\Events\PaymentRefunded;
use App\Domain\PlatformPayments\Events\PaymentRetryScheduled;
use App\Domain\PlatformPayments\Events\PaymentSettled;
use App\Domain\PlatformPayments\Events\SettlementReconciled;
use App\Domain\PlatformPayments\Infrastructure\EloquentAttemptRepository;
use App\Domain\PlatformPayments\Infrastructure\EloquentSettlementRepository;
use App\Domain\PlatformPayments\Infrastructure\EloquentTransactionRepository;
use App\Domain\PlatformPayments\Infrastructure\PaymentDimensionProvider;
use App\Domain\PlatformPayments\Infrastructure\PaymentGatewayImpl;
use App\Domain\PlatformPayments\Infrastructure\SettlementProviderImpl;
use App\Domain\PlatformPayments\Repositories\AttemptRepositoryInterface;
use App\Domain\PlatformPayments\Repositories\SettlementRepositoryInterface;
use App\Domain\PlatformPayments\Repositories\TransactionRepositoryInterface;
use App\Domain\PlatformPayments\Services\PaymentService;
use App\Domain\PlatformPayments\Services\RetryOrchestrator;
use App\Domain\PlatformPayments\Services\SettlementService;
use Illuminate\Support\ServiceProvider;

class PlatformPaymentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, PaymentGatewayImpl::class);
        $this->app->bind(SettlementProvider::class, SettlementProviderImpl::class);

        $this->app->bind(TransactionRepositoryInterface::class, EloquentTransactionRepository::class);
        $this->app->bind(AttemptRepositoryInterface::class, EloquentAttemptRepository::class);
        $this->app->bind(SettlementRepositoryInterface::class, EloquentSettlementRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/platform-payments'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'platform-payments',
            name: 'Platform Payments',
            version: '1.0.0',
            category: 'platform',
            type: 'platform',
            description: 'Payment processing, transaction management, settlement reconciliation, and retry orchestration',
            requiresOrganization: false,
            capabilities: ['payment_processing', 'settlement_reconciliation', 'transaction_management'],
            contracts: [PaymentGateway::class, SettlementProvider::class],
            permissions: ['process_payments', 'view_transactions', 'manage_settlements', 'refund_payments'],
            settings: ['default_gateway', 'max_retry_attempts', 'retry_backoff_hours', 'settlement_auto_reconcile'],
            events: [
                PaymentInitiated::class,
                PaymentAttemptFailed::class,
                PaymentCompleted::class,
                PaymentFailed::class,
                PaymentRefunded::class,
                PaymentSettled::class,
                PaymentRetryScheduled::class,
                SettlementReconciled::class,
            ],
            healthChecks: ['database'],
        ));

        $registry = $this->app->make(EventOwnershipRegistry::class);
        $registry->register(PaymentInitiated::NAME, 'platform-payments');
        $registry->register(PaymentAttemptFailed::NAME, 'platform-payments');
        $registry->register(PaymentCompleted::NAME, 'platform-payments');
        $registry->register(PaymentFailed::NAME, 'platform-payments');
        $registry->register(PaymentRefunded::NAME, 'platform-payments');
        $registry->register(PaymentSettled::NAME, 'platform-payments');
        $registry->register(PaymentRetryScheduled::NAME, 'platform-payments');
        $registry->register(SettlementReconciled::NAME, 'platform-payments');

        $this->app->make(DimensionResolver::class)->register(
            $this->app->make(PaymentDimensionProvider::class)
        );
    }
}
