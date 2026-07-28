<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Core\Services\DimensionResolver;
use App\Domain\Core\Services\ModuleDiscovery;
use App\Domain\Core\Services\EventOwnershipRegistry;
use App\Domain\Core\ValueObjects\ModuleManifest;
use App\Domain\GrowFinance\Infrastructure\GrowFinanceDimensionProvider;
use App\Domain\GrowFinance\Contracts\AccountingProvider;
use App\Domain\GrowFinance\Events\AccountBalanceChanged;
use App\Domain\GrowFinance\Events\BudgetUpdated;
use App\Domain\GrowFinance\Events\JournalPosted;
use App\Domain\GrowFinance\Events\PeriodClosed;
use App\Domain\GrowFinance\Events\ReportGenerated;
use App\Domain\GrowFinance\Infrastructure\AccountingProviderImpl;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\AccountingPeriodRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ApiTokenRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BankAccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BankStatementRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BankStatementLineRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BudgetRepositoryInterface;
use App\Domain\GrowFinance\Repositories\CustomerRepositoryInterface;
use App\Domain\GrowFinance\Repositories\DepreciationScheduleRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
use App\Domain\GrowFinance\Repositories\FiscalYearRepositoryInterface;
use App\Domain\GrowFinance\Repositories\FixedAssetRepositoryInterface;
use App\Domain\GrowFinance\Repositories\GroupConsolidationRepositoryInterface;
use App\Domain\GrowFinance\Repositories\OrgGroupRepositoryInterface;
use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\InvoiceItemRepositoryInterface;
use App\Domain\GrowFinance\Repositories\InvoiceTemplateRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\Repositories\PaymentRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ProfileRepositoryInterface;
use App\Domain\GrowFinance\Repositories\QuotationRepositoryInterface;
use App\Domain\GrowFinance\Repositories\QuotationItemRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ReconciliationMatchRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ReconciliationPeriodRepositoryInterface;
use App\Domain\GrowFinance\Repositories\RecurringTransactionRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ReportRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ReportScheduleRepositoryInterface;
use App\Domain\GrowFinance\Repositories\TaxRateRepositoryInterface;
use App\Domain\GrowFinance\Repositories\TaxReturnRepositoryInterface;
use App\Domain\GrowFinance\Repositories\TeamMemberRepositoryInterface;
use App\Domain\GrowFinance\Repositories\VendorRepositoryInterface;
use App\Domain\GrowFinance\Repositories\WhiteLabelRepositoryInterface;
use App\Domain\GrowFinance\Repositories\WorkflowTemplateRepositoryInterface;
use App\Domain\GrowFinance\Repositories\WorkflowInstanceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\IntercompanyTransactionRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BankConnectionRepositoryInterface;
use App\Domain\GrowFinance\Services\AccountingPeriodService;
use App\Domain\GrowFinance\Services\AccountingService;
use App\Domain\GrowFinance\Services\AgingReportService;
use App\Domain\GrowFinance\Services\AnomalyDetectionService;
use App\Domain\GrowFinance\Services\AutomatedRecommendationService;
use App\Domain\GrowFinance\Services\NaturalLanguageQueryService;
use App\Domain\GrowFinance\Services\ApiTokenService;
use App\Domain\GrowFinance\Services\DepreciationEngine;
use App\Domain\GrowFinance\Services\FixedAssetService;
use App\Domain\GrowFinance\Services\BankingService;
use App\Domain\GrowFinance\Services\BudgetService;
use App\Domain\GrowFinance\Services\CsvImportService;
use App\Domain\GrowFinance\Services\CurrencyConversionService;
use App\Domain\GrowFinance\Services\DashboardService;
use App\Domain\GrowFinance\Services\FxGainLossService;
use App\Domain\GrowFinance\Services\GeneralLedgerEngine;
use App\Domain\GrowFinance\Services\PastelMigrationService;
use App\Domain\GrowFinance\Services\ReportSnapshotService;
use App\Domain\GrowFinance\Services\InvoiceTemplateService;
use App\Domain\GrowFinance\Services\PdfInvoiceService;
use App\Domain\GrowFinance\Services\PostingEngine;
use App\Domain\GrowFinance\Services\QuotationService;
use App\Domain\GrowFinance\Services\ReconciliationService;
use App\Domain\GrowFinance\Services\PeriodEndService;
use App\Domain\GrowFinance\Services\RecurringTransactionService;
use App\Domain\GrowFinance\Services\ReportingEngine;
use App\Domain\GrowFinance\Services\ReportScheduleService;
use App\Domain\GrowFinance\Services\TaxEngine;
use App\Domain\GrowFinance\Services\TeamService;
use App\Domain\GrowFinance\Services\ZraEInvoiceService;
use App\Domain\GrowFinance\Services\ZraTaxReturnService;
use App\Domain\GrowFinance\Services\WhiteLabelService;
use App\Domain\GrowFinance\Services\WorkflowEngine;
use App\Domain\GrowFinance\Services\WorkflowEscalationService;
use App\Domain\GrowFinance\Services\ThreeWayMatchingService;
use App\Domain\GrowFinance\Services\ConsolidationService;
use App\Domain\GrowFinance\Services\DashboardWidgetService;
use App\Domain\GrowFinance\Services\ReportExportService;
use App\Domain\GrowFinance\Services\IntercompanyEliminationService;
use App\Domain\GrowFinance\Services\ProfitabilityService;
use App\Domain\GrowFinance\Services\FinancialRatioService;
use App\Domain\GrowFinance\Services\CashFlowForecastService;
use App\Domain\GrowFinance\Services\RevenuePredictionService;
use App\Domain\GrowFinance\Services\ScenarioModellingService;
use App\Domain\GrowFinance\Services\FiscalYearService;
use App\Domain\GrowFinance\Services\BankIntegrationService;
use App\Domain\GrowFinance\Repositories\ScenarioRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceCustomerModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceVendorModel;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentAccountRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentAccountingPeriodRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentApiTokenRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentBankAccountRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentBankStatementRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentBankStatementLineRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentBudgetRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentCustomerRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentDepreciationScheduleRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentExpenseRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentFiscalYearRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentGroupConsolidationRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentOrgGroupRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentFixedAssetRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentInvoiceRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentInvoiceItemRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentInvoiceTemplateRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentJournalEntryRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentJournalLineRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentPaymentRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentProfileRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentQuotationRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentQuotationItemRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentReconciliationMatchRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentReconciliationPeriodRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentRecurringTransactionRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentReportRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentReportScheduleRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentTaxRateRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentTaxReturnRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentTeamMemberRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentVendorRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentWhiteLabelRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentWorkflowTemplateRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentWorkflowInstanceRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentIntercompanyTransactionRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentScenarioRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentBankConnectionRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class GrowFinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(AccountRepositoryInterface::class, EloquentAccountRepository::class);
        $this->app->bind(AccountingPeriodRepositoryInterface::class, EloquentAccountingPeriodRepository::class);
        $this->app->bind(ApiTokenRepositoryInterface::class, EloquentApiTokenRepository::class);
        $this->app->bind(BankAccountRepositoryInterface::class, EloquentBankAccountRepository::class);
        $this->app->bind(BankStatementRepositoryInterface::class, EloquentBankStatementRepository::class);
        $this->app->bind(BankStatementLineRepositoryInterface::class, EloquentBankStatementLineRepository::class);
        $this->app->bind(BudgetRepositoryInterface::class, EloquentBudgetRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(DepreciationScheduleRepositoryInterface::class, EloquentDepreciationScheduleRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class, EloquentExpenseRepository::class);
        $this->app->bind(FiscalYearRepositoryInterface::class, EloquentFiscalYearRepository::class);
        $this->app->bind(FixedAssetRepositoryInterface::class, EloquentFixedAssetRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, EloquentInvoiceRepository::class);
        $this->app->bind(InvoiceItemRepositoryInterface::class, EloquentInvoiceItemRepository::class);
        $this->app->bind(InvoiceTemplateRepositoryInterface::class, EloquentInvoiceTemplateRepository::class);
        $this->app->bind(JournalEntryRepositoryInterface::class, EloquentJournalEntryRepository::class);
        $this->app->bind(JournalLineRepositoryInterface::class, EloquentJournalLineRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, EloquentProfileRepository::class);
        $this->app->bind(QuotationRepositoryInterface::class, EloquentQuotationRepository::class);
        $this->app->bind(QuotationItemRepositoryInterface::class, EloquentQuotationItemRepository::class);
        $this->app->bind(ReconciliationMatchRepositoryInterface::class, EloquentReconciliationMatchRepository::class);
        $this->app->bind(ReconciliationPeriodRepositoryInterface::class, EloquentReconciliationPeriodRepository::class);
        $this->app->bind(RecurringTransactionRepositoryInterface::class, EloquentRecurringTransactionRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, EloquentReportRepository::class);
        $this->app->bind(ReportScheduleRepositoryInterface::class, EloquentReportScheduleRepository::class);
        $this->app->bind(TaxRateRepositoryInterface::class, EloquentTaxRateRepository::class);
        $this->app->bind(TaxReturnRepositoryInterface::class, EloquentTaxReturnRepository::class);
        $this->app->bind(TeamMemberRepositoryInterface::class, EloquentTeamMemberRepository::class);
        $this->app->bind(VendorRepositoryInterface::class, EloquentVendorRepository::class);
        $this->app->bind(WhiteLabelRepositoryInterface::class, EloquentWhiteLabelRepository::class);
        $this->app->bind(WorkflowTemplateRepositoryInterface::class, EloquentWorkflowTemplateRepository::class);
        $this->app->bind(WorkflowInstanceRepositoryInterface::class, EloquentWorkflowInstanceRepository::class);
        $this->app->bind(OrgGroupRepositoryInterface::class, EloquentOrgGroupRepository::class);
        $this->app->bind(GroupConsolidationRepositoryInterface::class, EloquentGroupConsolidationRepository::class);
        $this->app->bind(IntercompanyTransactionRepositoryInterface::class, EloquentIntercompanyTransactionRepository::class);
        $this->app->bind(ScenarioRepositoryInterface::class, EloquentScenarioRepository::class);
        $this->app->bind(BankConnectionRepositoryInterface::class, EloquentBankConnectionRepository::class);

        // Services
        $this->app->singleton(AccountingPeriodService::class);
        $this->app->singleton(AccountingService::class);
        $this->app->singleton(AgingReportService::class);
        $this->app->singleton(ApiTokenService::class);
        $this->app->singleton(BankingService::class);
        $this->app->singleton(BudgetService::class);
        $this->app->singleton(CsvImportService::class);
        $this->app->singleton(CurrencyConversionService::class);
        $this->app->singleton(DashboardService::class);
        $this->app->singleton(FxGainLossService::class);
        $this->app->singleton(PastelMigrationService::class);
        $this->app->singleton(PeriodEndService::class);
        $this->app->singleton(ReportScheduleService::class);
        $this->app->singleton(ReportSnapshotService::class);
        $this->app->singleton(DepreciationEngine::class);
        $this->app->singleton(FixedAssetService::class);
        $this->app->singleton(GeneralLedgerEngine::class);
        $this->app->singleton(InvoiceTemplateService::class);
        $this->app->singleton(PdfInvoiceService::class);
        $this->app->singleton(PostingEngine::class);
        $this->app->singleton(QuotationService::class);
        $this->app->singleton(ReconciliationService::class);
        $this->app->singleton(RecurringTransactionService::class);
        $this->app->singleton(ReportingEngine::class);
        $this->app->singleton(TaxEngine::class);
        $this->app->singleton(TeamService::class);
        $this->app->singleton(WhiteLabelService::class);
        $this->app->singleton(WorkflowEngine::class);
        $this->app->singleton(WorkflowEscalationService::class);
        $this->app->singleton(ThreeWayMatchingService::class);
        $this->app->singleton(ConsolidationService::class);
        $this->app->singleton(DashboardWidgetService::class);
        $this->app->singleton(ReportExportService::class);
        $this->app->singleton(IntercompanyEliminationService::class);
        $this->app->singleton(ProfitabilityService::class);
        $this->app->singleton(FinancialRatioService::class);
        $this->app->singleton(CashFlowForecastService::class);
        $this->app->singleton(RevenuePredictionService::class);
        $this->app->singleton(AnomalyDetectionService::class);
        $this->app->singleton(NaturalLanguageQueryService::class);
        $this->app->singleton(AutomatedRecommendationService::class);
        $this->app->singleton(ScenarioModellingService::class);
        $this->app->singleton(FiscalYearService::class);
        $this->app->singleton(BankIntegrationService::class);

        // ZRA e-Invoicing & Electronic Tax Returns
        $this->app->singleton(ZraEInvoiceService::class);
        $this->app->singleton(ZraTaxReturnService::class);

        // Integration Contracts
        $this->app->bind(AccountingProvider::class, AccountingProviderImpl::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/growfinance'));

        $discovery = $this->app->make(ModuleDiscovery::class);
        $discovery->register(new ModuleManifest(
            id: 'growfinance',
            name: 'GrowFinance',
            version: '1.0.0',
            category: 'business',
            description: 'Accounting, invoicing, and financial management',
            requiresOrganization: true,
            capabilities: ['accounting', 'invoicing', 'financial_reports'],
            contracts: [AccountingProvider::class],
            permissions: ['manage_accounts', 'process_payments', 'view_reports', 'manage_budgets'],
            settings: ['default_currency', 'fiscal_year_start', 'tax_rate', 'invoice_prefix'],
            events: [
                \App\Domain\GrowFinance\Events\PaymentReceived::class,
                JournalPosted::class,
                AccountBalanceChanged::class,
                PeriodClosed::class,
                BudgetUpdated::class,
                ReportGenerated::class,
            ],
        ));

        $registry = $this->app->make(EventOwnershipRegistry::class);

        // Payment event (already existed but no NAME constant on class)
        $registry->register('growfinance.payment.received.v1', 'growfinance');

        // Renamed from growfinance.journal.created.v1 → growfinance.journal.posted.v1
        // Keep old name registered during transition period for backward compatibility
        $registry->register('growfinance.journal.created.v1', 'growfinance');
        $registry->register(JournalPosted::NAME, 'growfinance');

        // New financial events
        $registry->register(AccountBalanceChanged::NAME, 'growfinance');
        $registry->register(PeriodClosed::NAME, 'growfinance');
        $registry->register(BudgetUpdated::NAME, 'growfinance');
        $registry->register(ReportGenerated::NAME, 'growfinance');

        $this->app->make(DimensionResolver::class)->register(
            $this->app->make(GrowFinanceDimensionProvider::class)
        );

        Inertia::share([
            'quickEntryData' => function () {
                if (!Auth::check()) {
                    return null;
                }

                $businessId = Auth::id();

                $currentRoute = request()->route()?->getName() ?? '';
                if (!str_starts_with($currentRoute, 'growfinance.')) {
                    return null;
                }

                return [
                    'customers' => GrowFinanceCustomerModel::forBusiness($businessId)
                        ->active()
                        ->orderBy('name')
                        ->get(['id', 'name', 'email', 'phone']),
                    'vendors' => GrowFinanceVendorModel::forBusiness($businessId)
                        ->active()
                        ->orderBy('name')
                        ->get(['id', 'name']),
                    'expenseAccounts' => GrowFinanceAccountModel::forBusiness($businessId)
                        ->active()
                        ->ofType(AccountType::EXPENSE)
                        ->orderBy('code')
                        ->get(['id', 'code', 'name']),
                ];
            },
        ]);
    }
}
