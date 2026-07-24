<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ApiTokenRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BankAccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BankStatementRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BankStatementLineRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BudgetRepositoryInterface;
use App\Domain\GrowFinance\Repositories\CustomerRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
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
use App\Domain\GrowFinance\Repositories\TeamMemberRepositoryInterface;
use App\Domain\GrowFinance\Repositories\VendorRepositoryInterface;
use App\Domain\GrowFinance\Repositories\WhiteLabelRepositoryInterface;
use App\Domain\GrowFinance\Services\AccountingService;
use App\Domain\GrowFinance\Services\ApiTokenService;
use App\Domain\GrowFinance\Services\BankingService;
use App\Domain\GrowFinance\Services\BudgetService;
use App\Domain\GrowFinance\Services\DashboardService;
use App\Domain\GrowFinance\Services\InvoiceTemplateService;
use App\Domain\GrowFinance\Services\PdfInvoiceService;
use App\Domain\GrowFinance\Services\QuotationService;
use App\Domain\GrowFinance\Services\ReconciliationService;
use App\Domain\GrowFinance\Services\RecurringTransactionService;
use App\Domain\GrowFinance\Services\TeamService;
use App\Domain\GrowFinance\Services\WhiteLabelService;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceCustomerModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceVendorModel;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentAccountRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentApiTokenRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentBankAccountRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentBankStatementRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentBankStatementLineRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentBudgetRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentCustomerRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentExpenseRepository;
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
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentTeamMemberRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentVendorRepository;
use App\Infrastructure\Persistence\Repositories\GrowFinance\EloquentWhiteLabelRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class GrowFinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(AccountRepositoryInterface::class, EloquentAccountRepository::class);
        $this->app->bind(ApiTokenRepositoryInterface::class, EloquentApiTokenRepository::class);
        $this->app->bind(BankAccountRepositoryInterface::class, EloquentBankAccountRepository::class);
        $this->app->bind(BankStatementRepositoryInterface::class, EloquentBankStatementRepository::class);
        $this->app->bind(BankStatementLineRepositoryInterface::class, EloquentBankStatementLineRepository::class);
        $this->app->bind(BudgetRepositoryInterface::class, EloquentBudgetRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class, EloquentExpenseRepository::class);
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
        $this->app->bind(TeamMemberRepositoryInterface::class, EloquentTeamMemberRepository::class);
        $this->app->bind(VendorRepositoryInterface::class, EloquentVendorRepository::class);
        $this->app->bind(WhiteLabelRepositoryInterface::class, EloquentWhiteLabelRepository::class);

        // Services
        $this->app->singleton(AccountingService::class);
        $this->app->singleton(ApiTokenService::class);
        $this->app->singleton(BankingService::class);
        $this->app->singleton(BudgetService::class);
        $this->app->singleton(DashboardService::class);
        $this->app->singleton(InvoiceTemplateService::class);
        $this->app->singleton(PdfInvoiceService::class);
        $this->app->singleton(QuotationService::class);
        $this->app->singleton(ReconciliationService::class);
        $this->app->singleton(RecurringTransactionService::class);
        $this->app->singleton(TeamService::class);
        $this->app->singleton(WhiteLabelService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(database_path('migrations/growfinance'));

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
