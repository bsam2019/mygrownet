<?php

namespace App\Providers;

use App\Domain\Core\Contracts\ProviderContract;
use App\Domain\Core\Services\EventOwnershipRegistry;
use App\Domain\Core\Services\IntegrationRegistry;
use App\Domain\Core\Services\ModuleDiscovery;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\PlatformBilling\Contracts\BillingProvider;
use App\Domain\PlatformBilling\Events\GracePeriodExpiring;
use App\Domain\PlatformBilling\Events\InvoiceIssued;
use App\Domain\PlatformBilling\Events\PaymentDue;
use App\Domain\PlatformBilling\Events\SubscriptionCancelled;
use App\Domain\PlatformBilling\Events\SubscriptionCreated;
use App\Domain\PlatformBilling\Events\SubscriptionRenewed;
use App\Domain\PlatformBilling\Events\SubscriptionSuspended;
use App\Domain\PlatformBilling\Infrastructure\BillingProviderImpl;
use App\Domain\PlatformBilling\Infrastructure\EloquentInvoiceRepository;
use App\Domain\PlatformBilling\Infrastructure\EloquentPlanRepository;
use App\Domain\PlatformBilling\Infrastructure\EloquentSubscriptionRepository;
use App\Domain\PlatformBilling\Repositories\InvoiceRepositoryInterface;
use App\Domain\PlatformBilling\Repositories\PlanRepositoryInterface;
use App\Domain\PlatformBilling\Repositories\SubscriptionRepositoryInterface;
use App\Domain\PlatformBilling\Services\BillingService;
use App\Domain\Core\Services\DimensionResolver;
use App\Domain\PlatformBilling\Infrastructure\BillingDimensionProvider;
use App\Domain\PlatformBilling\Listeners\HandlePaymentCollectionFailed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class PlatformBillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BillingProvider::class, BillingProviderImpl::class);

        $this->app->bind(PlanRepositoryInterface::class, EloquentPlanRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, EloquentSubscriptionRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/platform-billing'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'platform-billing',
            name: 'Platform Billing',
            version: '1.0.0',
            category: 'platform',
            type: 'platform',
            description: 'Subscription management, billing, and invoice generation',
            requiresOrganization: false,
            capabilities: ['billing', 'subscription_management', 'invoice_generation'],
            contracts: [BillingProvider::class],
            permissions: ['manage_subscriptions', 'manage_plans', 'manage_invoices', 'view_billing'],
            settings: ['default_currency', 'grace_period_days', 'max_payment_failures', 'invoice_prefix'],
            events: [
                SubscriptionCreated::class,
                SubscriptionRenewed::class,
                SubscriptionSuspended::class,
                SubscriptionCancelled::class,
                InvoiceIssued::class,
                PaymentDue::class,
                GracePeriodExpiring::class,
            ],
            healthChecks: ['database'],
        ));

        $registry = $this->app->make(EventOwnershipRegistry::class);
        $registry->register(SubscriptionCreated::NAME, 'platform-billing');
        $registry->register(SubscriptionRenewed::NAME, 'platform-billing');
        $registry->register(SubscriptionSuspended::NAME, 'platform-billing');
        $registry->register(SubscriptionCancelled::NAME, 'platform-billing');
        $registry->register(InvoiceIssued::NAME, 'platform-billing');
        $registry->register(PaymentDue::NAME, 'platform-billing');
        $registry->register(GracePeriodExpiring::NAME, 'platform-billing');

        Event::listen('platform.payment.collection_failed.v1', HandlePaymentCollectionFailed::class);

        $this->app->make(DimensionResolver::class)->register(
            $this->app->make(BillingDimensionProvider::class)
        );
    }
}
