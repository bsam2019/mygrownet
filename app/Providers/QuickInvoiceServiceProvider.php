<?php

namespace App\Providers;

use App\Domain\QuickInvoice\Repositories\AdminSettingRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\AttachmentRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\DocumentRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\ProfileRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\SubscriptionTierRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\TemplateRepositoryInterface;
use App\Domain\QuickInvoice\Repositories\UsageTrackingRepositoryInterface;
use App\Domain\QuickInvoice\Services\AdminDashboardService;
use App\Domain\QuickInvoice\Services\AttachmentLibraryService;
use App\Domain\QuickInvoice\Services\DocumentService;
use App\Domain\QuickInvoice\Services\PdfGeneratorService;
use App\Domain\QuickInvoice\Services\PdfMergerService;
use App\Domain\QuickInvoice\Services\ProfileService;
use App\Domain\QuickInvoice\Services\ShareService;
use App\Domain\QuickInvoice\Services\SubscriptionService;
use App\Domain\QuickInvoice\Services\TemplateManagementService;
use App\Infrastructure\Persistence\Repositories\QuickInvoice\EloquentAdminSettingRepository;
use App\Infrastructure\Persistence\Repositories\QuickInvoice\EloquentAttachmentRepository;
use App\Infrastructure\Persistence\Repositories\QuickInvoice\EloquentDocumentRepository;
use App\Infrastructure\Persistence\Repositories\QuickInvoice\EloquentProfileRepository;
use App\Infrastructure\Persistence\Repositories\QuickInvoice\EloquentSubscriptionRepository;
use App\Infrastructure\Persistence\Repositories\QuickInvoice\EloquentSubscriptionTierRepository;
use App\Infrastructure\Persistence\Repositories\QuickInvoice\EloquentTemplateRepository;
use App\Infrastructure\Persistence\Repositories\QuickInvoice\EloquentUsageTrackingRepository;
use Illuminate\Support\ServiceProvider;

class QuickInvoiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(DocumentRepositoryInterface::class, EloquentDocumentRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, EloquentProfileRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, EloquentSubscriptionRepository::class);
        $this->app->bind(TemplateRepositoryInterface::class, EloquentTemplateRepository::class);
        $this->app->bind(AttachmentRepositoryInterface::class, EloquentAttachmentRepository::class);
        $this->app->bind(SubscriptionTierRepositoryInterface::class, EloquentSubscriptionTierRepository::class);
        $this->app->bind(AdminSettingRepositoryInterface::class, EloquentAdminSettingRepository::class);
        $this->app->bind(UsageTrackingRepositoryInterface::class, EloquentUsageTrackingRepository::class);

        // Domain services
        $this->app->singleton(DocumentService::class, function ($app) {
            return new DocumentService(
                $app->make(DocumentRepositoryInterface::class)
            );
        });

        $this->app->singleton(ProfileService::class, function ($app) {
            return new ProfileService(
                $app->make(ProfileRepositoryInterface::class)
            );
        });

        $this->app->singleton(SubscriptionService::class, function ($app) {
            return new SubscriptionService(
                $app->make(SubscriptionRepositoryInterface::class),
                $app->make(SubscriptionTierRepositoryInterface::class),
                $app->make(AdminSettingRepositoryInterface::class),
                $app->make(UsageTrackingRepositoryInterface::class)
            );
        });

        $this->app->singleton(TemplateManagementService::class, function ($app) {
            return new TemplateManagementService(
                $app->make(TemplateRepositoryInterface::class)
            );
        });

        $this->app->singleton(AttachmentLibraryService::class, function ($app) {
            return new AttachmentLibraryService(
                $app->make(AttachmentRepositoryInterface::class)
            );
        });

        $this->app->singleton(AdminDashboardService::class, function ($app) {
            return new AdminDashboardService(
                $app->make(UsageTrackingRepositoryInterface::class),
                $app->make(SubscriptionRepositoryInterface::class),
                $app->make(SubscriptionTierRepositoryInterface::class),
                $app->make(AdminSettingRepositoryInterface::class),
                $app->make(SubscriptionService::class)
            );
        });

        $this->app->singleton(PdfGeneratorService::class);
        $this->app->singleton(PdfMergerService::class);

        $this->app->singleton(ShareService::class, function ($app) {
            return new ShareService(
                $app->make(PdfGeneratorService::class)
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/quickinvoice'));
    }
}