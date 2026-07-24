<?php

namespace App\Http\Controllers\Admin\GrowNet;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\GrowNet\ReferralCommission;
use App\Models\ProfitShare;
use App\Infrastructure\Persistence\Eloquent\GrowNet\PointTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EarningsManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $users = User::select('id', 'name', 'email', 'phone', 'status', 'account_type', 'has_starter_kit', 'bonus_points', 'loyalty_points', 'bonus_balance', 'created_at')
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
                    'bonus_points' => $user->bonus_points,
                    'loyalty_points' => $user->loyalty_points,
                    'bonus_balance' => $user->bonus_balance,
                    'created_at' => $user->created_at->format('Y-m-d'),
                ];
            });

        return Inertia::render('Admin/GrowNet/EarningsManagement', [
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
                'lp_amount' => $pt->lp_amount,
                'bp_amount' => $pt->bp_amount,
                'description' => $pt->description,
                'created_at' => $pt->created_at->format('Y-m-d H:i:s'),
            ]);

        $transactions = Transaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'transaction_type' => $t->transaction_type,
                'amount' => (float) $t->amount,
                'status' => $t->status,
                'description' => $t->description,
                'created_at' => $t->created_at->format('Y-m-d H:i:s'),
            ]);

        $summary = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status,
            'account_type' => $user->account_type?->value,
            'has_starter_kit' => $user->has_starter_kit,
            'total_commissions' => (float) ReferralCommission::where('referrer_id', $user->id)->where('status', 'paid')->sum('amount'),
            'pending_commissions' => (float) ReferralCommission::where('referrer_id', $user->id)->where('status', 'pending')->sum('amount'),
            'total_profit_shares' => (float) ProfitShare::where('user_id', $user->id)->where('status', 'paid')->sum('amount'),
            'bonus_points' => $user->bonus_points,
            'bonus_balance' => $user->bonus_balance,
            'loyalty_points' => $user->loyalty_points,
            'lifetime_points' => $user->lifetime_points,
            'referral_count' => $user->referral_count,
        ];

        return Inertia::render('Admin/GrowNet/UserEarnings', [
            'user' => $summary,
            'commissions' => $commissions,
            'profitShares' => $profitShares,
            'pointTransactions' => $pointTransactions,
            'transactions' => $transactions,
        ]);
    }

    public function adjustBonusBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'reason' => 'required|string|max:500',
        ]);

        $amount = (float) $request->amount;

        DB::transaction(function () use ($user, $amount, $request) {
            $user->increment('bonus_balance', $amount);

            Transaction::create([
                'user_id' => $user->id,
                'amount' => abs($amount),
                'transaction_type' => $amount >= 0 ? 'bonus_award' : 'bonus_deduction',
                'status' => 'completed',
                'description' => $request->reason,
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
            ]);

            Log::info('Admin adjusted bonus_balance', [
                'user_id' => $user->id,
                'amount' => $amount,
                'reason' => $request->reason,
                'admin_id' => $request->user()->id,
            ]);
        });

        return back()->with('success', "Bonus balance adjusted by " . number_format($amount, 2));
    }
}
