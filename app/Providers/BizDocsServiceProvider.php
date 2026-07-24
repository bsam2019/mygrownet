<?php

namespace App\Providers;

use App\Application\BizDocs\UseCases\GenerateReceiptUseCase;
use App\Application\BizDocs\UseCases\GenerateStationeryUseCase;
use App\Application\BizDocs\UseCases\RecordPaymentUseCase;
use App\Domain\BizDocs\BusinessIdentity\Repositories\BusinessProfileRepositoryInterface;
use App\Domain\BizDocs\CustomerManagement\Repositories\CustomerRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentSequenceRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentStatusHistoryRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\Repositories\DocumentTemplateRepositoryInterface;
use App\Domain\BizDocs\DocumentManagement\Services\DocumentNumberingService;
use App\Infrastructure\BizDocs\Persistence\Repositories\EloquentBusinessProfileRepository;
use App\Infrastructure\BizDocs\Persistence\Repositories\EloquentCustomerRepository;
use App\Infrastructure\BizDocs\Persistence\Repositories\EloquentDocumentRepository;
use App\Infrastructure\BizDocs\Persistence\Repositories\EloquentDocumentSequenceRepository;
use App\Infrastructure\BizDocs\Persistence\Repositories\EloquentDocumentStatusHistoryRepository;
use App\Infrastructure\BizDocs\Persistence\Repositories\EloquentDocumentTemplateRepository;
use Illuminate\Support\ServiceProvider;

class BizDocsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            DocumentRepositoryInterface::class,
            EloquentDocumentRepository::class
        );

        $this->app->bind(
            BusinessProfileRepositoryInterface::class,
            EloquentBusinessProfileRepository::class
        );

        $this->app->bind(
            CustomerRepositoryInterface::class,
            EloquentCustomerRepository::class
        );

        $this->app->bind(
            DocumentTemplateRepositoryInterface::class,
            EloquentDocumentTemplateRepository::class
        );

        $this->app->bind(
            DocumentSequenceRepositoryInterface::class,
            EloquentDocumentSequenceRepository::class
        );

        $this->app->bind(
            DocumentStatusHistoryRepositoryInterface::class,
            EloquentDocumentStatusHistoryRepository::class
        );

        $this->app->bind(GenerateReceiptUseCase::class, function ($app) {
            return new GenerateReceiptUseCase(
                $app->make(DocumentRepositoryInterface::class),
                $app->make(DocumentNumberingService::class)
            );
        });

        $this->app->bind(RecordPaymentUseCase::class, function ($app) {
            return new RecordPaymentUseCase(
                $app->make(DocumentRepositoryInterface::class),
                $app->make(GenerateReceiptUseCase::class)
            );
        });

        $this->app->bind(\App\Application\BizDocs\UseCases\CancelDocumentUseCase::class, function ($app) {
            return new \App\Application\BizDocs\UseCases\CancelDocumentUseCase(
                $app->make(DocumentRepositoryInterface::class)
            );
        });

        $this->app->bind(\App\Application\BizDocs\UseCases\VoidDocumentUseCase::class, function ($app) {
            return new \App\Application\BizDocs\UseCases\VoidDocumentUseCase(
                $app->make(DocumentRepositoryInterface::class)
            );
        });

        $this->app->bind(\App\Application\BizDocs\UseCases\ConvertQuotationToInvoiceUseCase::class, function ($app) {
            return new \App\Application\BizDocs\UseCases\ConvertQuotationToInvoiceUseCase(
                $app->make(DocumentRepositoryInterface::class),
                $app->make(\App\Application\BizDocs\UseCases\Document\CreateDocumentUseCase::class)
            );
        });

        $this->app->bind(\App\Application\BizDocs\UseCases\DuplicateDocumentUseCase::class, function ($app) {
            return new \App\Application\BizDocs\UseCases\DuplicateDocumentUseCase(
                $app->make(DocumentRepositoryInterface::class),
                $app->make(\App\Application\BizDocs\UseCases\Document\CreateDocumentUseCase::class)
            );
        });

        $this->app->bind(GenerateStationeryUseCase::class, function ($app) {
            return new GenerateStationeryUseCase(
                $app->make(BusinessProfileRepositoryInterface::class),
                $app->make(DocumentTemplateRepositoryInterface::class),
                $app->make(\App\Application\BizDocs\Services\StationeryGeneratorService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/bizdocs'));
    }
}