<?php

namespace App\Providers;

use App\Domain\Investor\Repositories\InvestorInquiryRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorAccountRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorInquiryRepository;
use App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorAccountRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Investor Service Provider
 * 
 * Registers investor domain services and repository bindings
 */
class InvestorServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        $this->app->bind(
            InvestorInquiryRepositoryInterface::class,
            EloquentInvestorInquiryRepository::class
        );
        
        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestmentRoundRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestmentRoundRepository::class
        );
        
        $this->app->bind(
            InvestorAccountRepositoryInterface::class,
            EloquentInvestorAccountRepository::class
        );
        
        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorDocumentRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorDocumentRepository::class
        );
        
        $this->app->bind(
            \App\Domain\Investor\Repositories\FinancialReportRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentFinancialReportRepository::class
        );
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        //
    }
}
