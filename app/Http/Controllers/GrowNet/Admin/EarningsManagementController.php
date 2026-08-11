<?php

namespace App\Http\Controllers\GrowNet\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\GrowNet\ReferralCommission;
use App\Models\ProfitShare;
use App\Infrastructure\Persistence\Eloquent\GrowNet\PointTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EarningsManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $users = User::select('id', 'name', 'email', 'phone', 'status', 'account_type', 'has_starter_kit', 'monthly_points', 'loyalty_points', 'bonus_balance', 'created_at')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(function ($user) {
                $commissions = (float) ReferralCommission::where('referrer_id', $user->id)->where('status', 'paid')->sum('amount');
                $pendingComms = (float) ReferralCommission::where('referrer_id', $user->id)->where('status', 'pending')->sum('amount');
                $profitShares = (float) ProfitShare::where('user_id', $user->id)->where('status', 'paid')->sum('amount');
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'account_type' => $user->account_type?->value,
                    'has_starter_kit' => $user->has_starter_kit,
                    'total_earnings' => $commissions + $profitShares,
                    'pending_earnings' => $pendingComms,
                    'commissions' => $commissions,
                    'profit_shares' => $profitShares,
                    'monthly_points' => $user->monthly_points,
                    'loyalty_points' => $user->loyalty_points,
                    'bonus_balance' => $user->bonus_balance,
                    'created_at' => $user->created_at->format('Y-m-d'),
                ];
            });

        return Inertia::render('GrowNet/Admin/EarningsManagement', [
            'users' => $users,
            'filters' => ['search' => $search, 'status' => $status],
        ]);
    }

    public function show(Request $request, User $user)
    {
        $commissions = ReferralCommission::where('referrer_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'level' => $c->level,
                'amount' => (float) $c->amount,
                'status' => $c->status,
                'commission_type' => $c->commission_type,
                'package_type' => $c->package_type,
                'paid_at' => $c->paid_at?->format('Y-m-d H:i:s'),
                'created_at' => $c->created_at->format('Y-m-d'),
            ]);

        $profitShares = ProfitShare::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'amount' => (float) $p->amount,
                'status' => $p->status,
                'distribution_type' => $p->distribution_type,
                'tier_at_distribution' => $p->tier_at_distribution,
                'paid_at' => $p->paid_at?->format('Y-m-d H:i:s'),
                'created_at' => $p->created_at->format('Y-m-d'),
            ]);

        $pointTransactions = PointTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($pt) => [
                'id' => $pt->id,
                'source' => $pt->source,
                'type' => $pt->type,
                'lp_amount' => $pt->lp_amount ?? 0,
                'bp_amount' => $pt->bp_amount ?? 0,
                'description' => $pt->description ?? '',
                'created_at' => $pt->created_at->format('Y-m-d H:i:s'),
            ]);

        $totalCommissions = (float) ReferralCommission::where('referrer_id', $user->id)->where('status', 'paid')->sum('amount');
        $pendingCommissions = (float) ReferralCommission::where('referrer_id', $user->id)->where('status', 'pending')->sum('amount');
        $totalProfitShares = (float) ProfitShare::where('user_id', $user->id)->where('status', 'paid')->sum('amount');
        $referralCount = User::where('referred_by', $user->id)->count();

        $transactions = [];

        return Inertia::render('GrowNet/Admin/UserEarnings', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
                'account_type' => $user->account_type?->value ?? 'Standard',
                'has_starter_kit' => (bool) $user->has_starter_kit,
                'total_commissions' => $totalCommissions,
                'pending_commissions' => $pendingCommissions,
                'total_profit_shares' => $totalProfitShares,
                'bonus_points' => (int) ($user->monthly_points ?? 0),
                'bonus_balance' => (float) ($user->bonus_balance ?? 0),
                'loyalty_points' => (float) ($user->loyalty_points ?? 0),
                'lifetime_points' => (int) ($user->lifetime_points ?? 0),
                'referral_count' => $referralCount,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ],
            'commissions' => $commissions,
            'profitShares' => $profitShares,
            'pointTransactions' => $pointTransactions,
            'transactions' => $transactions,
        ]);
    }
}
