<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\Core\Services\OrganizationEntryResolver;
use App\Domain\GrowFinance\Services\DashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly OrganizationEntryResolver $orgResolver,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $businessId = $user->id;

        // Company details can be entered once at the platform organization and
        // flow into GrowFinance rather than being re-entered here. Resolved
        // from the active org context (decoupled) with an empty fallback when
        // no organization is active.
        $companyDetails = $this->orgResolver->companyDetails($user);

        // Check if setup is complete
        if (!$this->dashboardService->hasSetupCompleted($businessId)) {
            return Inertia::render('GrowFinance/Setup/Index', [
                'companyDetails' => $companyDetails,
            ]);
        }

        $financialSummary = $this->dashboardService->getFinancialSummary($businessId);
        $invoiceStats = $this->dashboardService->getInvoiceStats($businessId);
        $recentTransactions = $this->dashboardService->getRecentTransactions($businessId);
        $overdueInvoices = $this->dashboardService->getOverdueInvoices($businessId);
        $expensesByCategory = $this->dashboardService->getExpensesByCategory($businessId);
        $monthlyTrend = $this->dashboardService->getMonthlyTrend($businessId);

        return Inertia::render('GrowFinance/Dashboard', [
            'financialSummary' => $financialSummary,
            'invoiceStats' => $invoiceStats,
            'recentTransactions' => $recentTransactions,
            'overdueInvoices' => $overdueInvoices,
            'expensesByCategory' => $expensesByCategory,
            'monthlyTrend' => $monthlyTrend,
            'companyDetails' => $companyDetails,
        ]);
    }
}
