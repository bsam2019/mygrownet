<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\PrimeEdge\Repositories\ClientRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\ServiceRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\InquiryRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\EngagementRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\ComplianceTaskRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\DocumentRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\InvoiceRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\AppointmentRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\ReferralPartnerRepositoryInterface;
use App\Infrastructure\PrimeEdge\Repositories\EloquentClientRepository;
use App\Infrastructure\PrimeEdge\Repositories\EloquentServiceRepository;
use App\Infrastructure\PrimeEdge\Repositories\EloquentInquiryRepository;
use App\Infrastructure\PrimeEdge\Repositories\EloquentEngagementRepository;
use App\Infrastructure\PrimeEdge\Repositories\EloquentComplianceTaskRepository;
use App\Infrastructure\PrimeEdge\Repositories\EloquentDocumentRepository;
use App\Infrastructure\PrimeEdge\Repositories\EloquentInvoiceRepository;
use App\Infrastructure\PrimeEdge\Repositories\EloquentAppointmentRepository;
use App\Infrastructure\PrimeEdge\Repositories\EloquentReferralPartnerRepository;

class PrimeEdgeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClientRepositoryInterface::class, EloquentClientRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, EloquentServiceRepository::class);
        $this->app->bind(InquiryRepositoryInterface::class, EloquentInquiryRepository::class);
        $this->app->bind(EngagementRepositoryInterface::class, EloquentEngagementRepository::class);
        $this->app->bind(ComplianceTaskRepositoryInterface::class, EloquentComplianceTaskRepository::class);
        $this->app->bind(DocumentRepositoryInterface::class, EloquentDocumentRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
        $this->app->bind(AppointmentRepositoryInterface::class, EloquentAppointmentRepository::class);
        $this->app->bind(ReferralPartnerRepositoryInterface::class, EloquentReferralPartnerRepository::class);

        $this->app->singleton(\App\Domain\PrimeEdge\Services\ClientOnboardingService::class);
        $this->app->singleton(\App\Domain\PrimeEdge\Services\InquiryService::class);
        $this->app->singleton(\App\Domain\PrimeEdge\Services\EngagementService::class);
        $this->app->singleton(\App\Domain\PrimeEdge\Services\ComplianceMonitoringService::class);
        $this->app->singleton(\App\Domain\PrimeEdge\Services\BillingService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/prime_edge'));
        $this->mergeConfigFrom(config_path('modules/primeedge.php'), 'modules.primeedge');
    }
}
