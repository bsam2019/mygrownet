<?php

namespace App\Providers;

use App\Domain\QuickInvoice\Repositories\DocumentRepositoryInterface;
use App\Domain\QuickInvoice\Services\DocumentService;
use App\Domain\QuickInvoice\Services\PdfGeneratorService;
use App\Domain\QuickInvoice\Services\ShareService;
use App\Infrastructure\QuickInvoice\Repositories\EloquentDocumentRepository;
use Illuminate\Support\ServiceProvider;

class QuickInvoiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repository interface to implementation
        $this->app->bind(
            DocumentRepositoryInterface::class,
            EloquentDocumentRepository::class
        );

        // Register domain services
        $this->app->singleton(DocumentService::class, function ($app) {
            return new DocumentService(
                $app->make(DocumentRepositoryInterface::class)
            );
        });

        $this->app->singleton(PdfGeneratorService::class);

        $this->app->singleton(ShareService::class, function ($app) {
            return new ShareService(
                $app->make(PdfGeneratorService::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
