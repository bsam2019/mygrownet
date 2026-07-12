<?php

namespace App\Http\Controllers\Admin\GrowNet;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\ProfitShare;
use App\Models\TeamVolume;
use App\Models\LGR\LgrSetting;
use App\Services\MLMAdministrationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class GrowNetDashboardController extends Controller
{
    public function __construct(
        protected MLMAdministrationService $mlmAdminService
    ) {}

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        $user = $request->user();

        // Aggregated KPIs
        $totalMembers = User::count();
        $activeMembers = User::where('status', 'active')->count();
        $membersWithKit = User::where('has_starter_kit', true)->count();

        $totalCommissionsPaid = (float) ReferralCommission::where('status', 'paid')->sum('amount');
        $pendingCommissions = (float) ReferralCommission::where('status', 'pending')->sum('amount');
        $pendingCount = ReferralCommission::where('status', 'pending')->count();

        $totalProfitShares = (float) ProfitShare::sum('amount');
        $pendingProfitShares = (float) ProfitShare::where('status', 'calculated')->sum('amount');

        // This period
        $periodStart = match($period) {
            'week' => now()->startOfWeek(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $periodCommissions = (float) ReferralCommission::where('status', 'paid')
            ->where('created_at', '>=', $periodStart)
            ->sum('amount');

        $periodNewMembers = User::where('created_at', '>=', $periodStart)->count();
        $periodKitPurchases = User::where('has_starter_kit', true)
            ->where('updated_at', '>=', $periodStart)
            ->count();

        // Monthly trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $monthlyTrend[] = [
                'month' => $month->format('M Y'),
                'commissions' => (float) ReferralCommission::where('status', 'paid')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('amount'),
                'new_members' => User::whereBetween('created_at', [$start, $end])->count(),
                'profit_shares' => (float) ProfitShare::where('status', 'paid')
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('amount'),
            ];
        }

        // Top earners
        $topEarners = User::select('users.id', 'users.name', 'users.email', 'users.bonus_points')
            ->selectRaw('(SELECT COALESCE(SUM(amount), 0) FROM referral_commissions WHERE referrer_id = users.id AND status = ?) as total_earnings', ['paid'])
            ->where('status', 'active')
            ->orderByDesc('total_earnings')
            ->limit(10)
            ->get()
            ->toArray();

        // LGR stats
        $lgrAwardedTotal = (float) User::sum('loyalty_points_awarded_total');
        $lgrCurrentBalance = (float) User::sum('loyalty_points');
        $lgrWithdrawnTotal = (float) User::sum('loyalty_points_withdrawn_total');

        // Volume stats
        $totalTeamVolume = (float) TeamVolume::sum('team_volume');
        $periodTeamVolume = (float) TeamVolume::where('period_start', '>=', $periodStart)->sum('team_volume');

        return Inertia::render('Admin/GrowNet/Dashboard', [
            'kpis' => [
                'total_members' => $totalMembers,
                'active_members' => $activeMembers,
                'members_with_kit' => $membersWithKit,
                'total_commissions_paid' => $totalCommissionsPaid,
                'pending_commissions' => $pendingCommissions,
                'pending_commissions_count' => $pendingCount,
                'total_profit_shares' => $totalProfitShares,
                'pending_profit_shares' => $pendingProfitShares,
                'lgr_awarded_total' => $lgrAwardedTotal,
                'lgr_current_balance' => $lgrCurrentBalance,
                'lgr_withdrawn_total' => $lgrWithdrawnTotal,
                'total_team_volume' => $totalTeamVolume,
            ],
            'periodMetrics' => [
                'period' => $period,
                'commissions' => $periodCommissions,
                'new_members' => $periodNewMembers,
                'kit_purchases' => $periodKitPurchases,
                'team_volume' => $periodTeamVolume,
            ],
            'monthlyTrend' => $monthlyTrend,
            'topEarners' => $topEarners,
            'mlmOverview' => $this->mlmAdminService->getOverviewMetrics($period),
        ]);
    }
}
