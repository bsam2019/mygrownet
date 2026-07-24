<?php

namespace App\Http\Controllers\GrowFinance;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceBankAccountModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceBankStatementModel;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceReconciliationPeriodModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $businessId = $request->attributes->get('business_id') ?? session('current_business_id') ?? auth()->id();

        $accounts = GrowFinanceBankAccountModel::forBusiness($businessId)->active()->get();

        $totalBalance = $accounts->sum('current_balance');
        $accountCount = $accounts->count();
        $avgBalance = $accountCount > 0 ? $totalBalance / $accountCount : 0;

        $accountIds = $accounts->pluck('id');

        $reconciliationPeriods = GrowFinanceReconciliationPeriodModel::whereIn('bank_account_id', $accountIds)->get();
        $activeReconciliations = $reconciliationPeriods->where('status', 'in_progress')->count();
        $completedReconciliations = $reconciliationPeriods->where('status', 'completed')->count();

        $recentStatements = GrowFinanceBankStatementModel::whereIn('bank_account_id', $accountIds)
            ->with('bankAccount')
            ->latest()->limit(10)->get();

        $totalsByAccount = $accounts->map(fn($a) => [
            'name' => $a->account_name,
            'bank' => $a->bank_name,
            'balance' => $a->current_balance,
            'type' => $a->account_type,
        ]);

        return Inertia::render('GrowFinance/Analytics/Index', [
            'accounts' => $totalsByAccount,
            'summary' => [
                'total_balance' => $totalBalance,
                'account_count' => $accountCount,
                'average_balance' => $avgBalance,
                'active_reconciliations' => $activeReconciliations,
                'completed_reconciliations' => $completedReconciliations,
            ],
            'recent_statements' => $recentStatements,
        ]);
    }
}
