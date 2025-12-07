<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\DashboardService;
use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $businessId = $user->id;

        // Check if setup is complete
        if (!$this->dashboardService->hasSetupCompleted($businessId)) {
            return Inertia::render('GrowFinance/Setup/Index');
        }

        $financialSummary = $this->dashboardService->getFinancialSummary($businessId);
        $invoiceStats = $this->dashboardService->getInvoiceStats($businessId);
        $recentTransactions = $this->dashboardService->getRecentTransactions($businessId);
        $overdueInvoices = $this->dashboardService->getOverdueInvoices($businessId);
        $expensesByCategory = $this->dashboardService->getExpensesByCategory($businessId);
        $monthlyTrend = $this->dashboardService->getMonthlyTrend($businessId);

        // Get subscription and usage data
        $usageSummary = $this->subscriptionService->getUsageSummary($user);
        $subscription = [
            'tier' => $usageSummary['tier'],
            'tier_name' => $usageSummary['tier_name'],
        ];

        return Inertia::render('GrowFinance/Dashboard', [
            'financialSummary' => $financialSummary,
            'invoiceStats' => $invoiceStats,
            'recentTransactions' => $recentTransactions,
            'overdueInvoices' => $overdueInvoices,
            'expensesByCategory' => $expensesByCategory,
            'monthlyTrend' => $monthlyTrend,
            'usageSummary' => $usageSummary,
            'subscription' => $subscription,
        ]);
    }
}
