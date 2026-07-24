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
        
        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorAnnouncementRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorAnnouncementRepository::class
        );
        
        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorMessageRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorMessageRepository::class
        );
        
        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorNotificationPreferenceRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorNotificationPreferenceRepository::class
        );
        
        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorEmailLogRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorEmailLogRepository::class
        );
        
        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorQuestionRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorQuestionRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorQuestionAnswerRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorQuestionAnswerRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorFeedbackRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorFeedbackRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorSurveyRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorSurveyRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorSurveyResponseRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorSurveyResponseRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorPollRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorPollRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorPollVoteRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorPollVoteRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorDividendRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorDividendRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\InvestorPaymentMethodRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentInvestorPaymentMethodRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\CompanyValuationRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentCompanyValuationRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\RiskAssessmentRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentRiskAssessmentRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\ScenarioModelRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentScenarioModelRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\ExitProjectionRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentExitProjectionRepository::class
        );

        $this->app->bind(
            \App\Domain\Investor\Repositories\QuarterlyReportRepositoryInterface::class,
            \App\Infrastructure\Persistence\Repositories\Investor\EloquentQuarterlyReportRepository::class
        );

        // Register messaging service as singleton
        $this->app->singleton(
            \App\Domain\Investor\Services\InvestorMessagingService::class,
            \App\Domain\Investor\Services\InvestorMessagingService::class
        );
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/investor'));
    }
}
