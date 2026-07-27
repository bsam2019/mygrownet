<?php

namespace App\Providers;

use App\Domain\Core\Contracts\HealthService;
use App\Domain\Core\Contracts\IdentityProvider;
use App\Domain\Core\Contracts\MediaProvider;
use App\Domain\Core\Contracts\NotificationProvider;
use App\Domain\Core\Infrastructure\MediaProviderImpl;
use App\Domain\Core\Infrastructure\NotificationProviderImpl;
use App\Domain\Core\Services\AlertService;
use App\Domain\Core\Services\ApiGateway;
use App\Domain\Core\Services\ApplicationLifecycleService;
use App\Domain\Core\Services\ApplicationProvisioningService;
use App\Domain\Core\Contracts\IntegrationEventDispatcher;
use App\Domain\Core\Services\CacheKeyHelper;
use App\Domain\Core\Services\DimensionResolver;
use App\Domain\Core\Services\LaravelEventDispatcher;
use App\Domain\Core\Services\CapabilityRegistry;
use App\Domain\Core\Services\ContractInvoker;
use App\Domain\Core\Services\ContractResolver;
use App\Domain\Core\Services\DataOwnershipRegistry;
use App\Domain\Core\Services\DeadLetterService;
use App\Domain\Core\Contracts\EventTransport;
use App\Domain\Core\Services\EventDispatcher;
use App\Domain\Core\Services\EventOwnershipRegistry;
use App\Domain\Core\Services\MessageQueueTransport;
use App\Domain\Core\Services\EventReplayService;
use App\Domain\Core\Services\FeatureFlagService;
use App\Domain\Core\Services\HealthServiceImpl;
use App\Domain\Core\Services\InboxService;
use App\Domain\Core\Services\IntegrationGuard;
use App\Domain\Core\Services\IntegrationRegistry;
use App\Domain\Core\Services\LaravelIdentityProvider;
use App\Domain\Core\Services\ManifestValidator;
use App\Domain\Core\Services\MetricsService;
use App\Domain\Core\Services\ModuleDiscovery;
use App\Domain\Core\Services\OutboxService;
use App\Domain\Core\Services\QueueService;
use App\Domain\Core\Services\SettingsService;
use App\Domain\Platform\Contracts\ServiceRegistry;
use App\Domain\Platform\Services\InProcessServiceRegistry;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\Support\Repositories\TicketRepository;
use App\Domain\Support\Repositories\TicketCommentRepository;
use App\Infrastructure\Persistence\Eloquent\Support\EloquentTicketRepository;
use App\Infrastructure\Persistence\Eloquent\Support\EloquentTicketCommentRepository;
use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Phase 1: Identity & Core
        $this->app->bind(IdentityProvider::class, LaravelIdentityProvider::class);
        $this->app->singleton(ModuleDiscovery::class);

        // Phase 2: Event infrastructure
        $this->app->singleton(EventOwnershipRegistry::class);
        $this->app->singleton(EventDispatcher::class, function ($app) {
            return new EventDispatcher(
                registry: $app->make(EventOwnershipRegistry::class),
                transport: $app->make(EventTransport::class),
            );
        });

        // Phase 1: Platform services
        $this->app->singleton(\App\Domain\Core\Services\PlatformContextResolver::class);
        $this->app->singleton(\App\Domain\Core\Services\ApplicationService::class);

        // Phase 3: Integration contracts
        $this->app->singleton(IntegrationRegistry::class);
        $this->app->singleton(ContractResolver::class);
        $this->app->singleton(IntegrationGuard::class);
        $this->app->singleton(ContractInvoker::class);
        $this->app->bind(NotificationProvider::class, NotificationProviderImpl::class);
        $this->app->bind(MediaProvider::class, MediaProviderImpl::class);

        // Stage 3: Cross-process event transport
        $this->app->singleton(EventTransport::class, function ($app) {
            return new MessageQueueTransport(
                connection: $app->make('config')->get('queue.default', 'default'),
                queue: $app->make('config')->get('platform.queue.default_queue', 'events'),
            );
        });

        // Phase 4: Platform integration services
        $this->app->singleton(ApplicationProvisioningService::class);
        $this->app->singleton(ApplicationLifecycleService::class);
        $this->app->singleton(CapabilityRegistry::class);
        $this->app->singleton(FeatureFlagService::class);
        $this->app->bind(HealthService::class, HealthServiceImpl::class);
        $this->app->singleton(ManifestValidator::class);

        // Phase 5: Operational readiness
        $this->app->bind(IntegrationEventDispatcher::class, LaravelEventDispatcher::class);
        $this->app->singleton(DeadLetterService::class);
        $this->app->singleton(QueueService::class);
        $this->app->singleton(MetricsService::class);
        $this->app->singleton(AlertService::class);

        // Phase 6: Data governance & tenant isolation
        $this->app->singleton(DataOwnershipRegistry::class);
        $this->app->singleton(SettingsService::class);
        $this->app->singleton(CacheKeyHelper::class);
        $this->app->singleton(DimensionResolver::class);

        // Phase 7: Reliable event delivery
        $this->app->singleton(OutboxService::class);
        $this->app->singleton(InboxService::class);
        $this->app->singleton(EventReplayService::class);

        // Stage 3: Service Registry & API Gateway
        $this->app->singleton(ServiceRegistry::class, function ($app) {
            return new InProcessServiceRegistry();
        });
        $this->app->singleton(ApiGateway::class, function ($app) {
            return new ApiGateway(
                registry: $app->make(IntegrationRegistry::class),
                serviceRegistry: $app->make(ServiceRegistry::class),
                invoker: $app->make(ContractInvoker::class),
            );
        });

        // Support domain (part of Core)
        $this->app->bind(TicketRepository::class, EloquentTicketRepository::class);
        $this->app->bind(TicketCommentRepository::class, EloquentTicketCommentRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/core'));
        $this->loadMigrationsFrom(database_path('migrations/support'));

        $registry = $this->app->make(EventOwnershipRegistry::class);

        // Platform lifecycle events (§10.7)
        $registry->register('platform.organization.created.v1', 'platform-core');
        $registry->register('platform.organization.member_added.v1', 'platform-core');
        $registry->register('platform.organization.member_removed.v1', 'platform-core');
        $registry->register('platform.application.enabled.v1', 'platform-core');
        $registry->register('platform.application.disabled.v1', 'platform-core');
        $registry->register('platform.application.maintenance.v1', 'platform-core');
        $registry->register('platform.application.archived.v1', 'platform-core');
        $registry->register('platform.contract.resolved.v1', 'platform-core');
        $registry->register('platform.contract.failed.v1', 'platform-core');
        $registry->register('platform.failure.circuit_broken.v1', 'platform-core');

        // Integration events (§10.3)
        $registry->register('platform.integration.contract_timeout.v1', 'platform-core');
        $registry->register('platform.integration.event_delivery_failed.v1', 'platform-core');
        $registry->register('platform.integration.provider_unhealthy.v1', 'platform-core');

        // BMS domain events (§10.7) — invoice events moved to BmsServiceProvider
        $registry->register('bms.employee.created.v1', 'bms');

        // StockFlow domain events (§10.7)
        $registry->register('stockflow.purchase_order.created.v1', 'stockflow');
        $registry->register('stockflow.goods_received.v1', 'stockflow');
        $registry->register('stockflow.stock.adjusted.v1', 'stockflow');

        // GrowFinance domain events (§10.7)
        $registry->register('growfinance.payment.received.v1', 'growfinance');
        $registry->register('growfinance.journal.created.v1', 'growfinance');

        // Phase 7: Reliable event delivery events
        $registry->register('platform.outbox.event_published.v1', 'platform-core');
        $registry->register('platform.outbox.event_failed.v1', 'platform-core');
        $registry->register('platform.inbox.event_processed.v1', 'platform-core');
        $registry->register('platform.inbox.event_duplicate.v1', 'platform-core');

        // Register platform-core manifest
        $discovery = $this->app->make(ModuleDiscovery::class);
        $manifest = new ModuleManifest(
            id: 'platform-core',
            name: 'Platform Core',
            version: '1.0.0',
            category: 'platform',
            type: 'platform',
            description: 'User identity, organizations, applications, support ticketing, and integration infrastructure',
            requiresOrganization: false,
            capabilities: ['identity', 'organization_management', 'application_management', 'feature_flags', 'event_outbox', 'event_inbox', 'event_replay', 'support'],
            contracts: [
                IdentityProvider::class,
                NotificationProvider::class,
                MediaProvider::class,
            ],
            permissions: ['manage_organizations', 'manage_applications', 'manage_users', 'manage_platform', 'manage_tickets'],
            settings: ['session_lifetime', 'session_domain', 'maintenance_mode', 'ticket_default_priority'],
            events: [
                \App\Domain\Core\Events\OrganizationCreated::class,
                \App\Domain\Core\Events\OrganizationArchived::class,
                \App\Domain\Core\Events\MemberAdded::class,
                \App\Domain\Core\Events\OrganizationMemberRemoved::class,
                \App\Domain\Core\Events\ApplicationSubscribed::class,
                \App\Domain\Core\Events\ApplicationEnabled::class,
                \App\Domain\Core\Events\ApplicationDisabled::class,
                \App\Domain\Core\Events\ApplicationMaintenance::class,
                \App\Domain\Core\Events\ApplicationArchived::class,
            ],
            healthChecks: ['database', 'cache', 'queue'],
        );
        $discovery->register($manifest);

        // Validate all registered manifests at boot
        $validator = $this->app->make(ManifestValidator::class);
        foreach ($discovery->allManifests() as $manifest) {
            $validator->validate($manifest);
        }
    }
}
