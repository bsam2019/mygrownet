<?php

namespace App\Providers;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Domain\GrowFinance\Services\BankingService;
use App\Domain\GrowFinance\Services\DashboardService;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceCustomerModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinanceVendorModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class GrowFinanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AccountingService::class);
        $this->app->singleton(BankingService::class);
        $this->app->singleton(DashboardService::class);
    }

    public function boot(): void
    {
        // Share quick entry data for modals across all GrowFinance pages
        Inertia::share([
            'quickEntryData' => function () {
                if (!Auth::check()) {
                    return null;
                }

                $businessId = Auth::id();

                // Only load if we're on a GrowFinance route
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
