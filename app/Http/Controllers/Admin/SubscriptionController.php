<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['currentSubscription.package', 'transactions' => function($q) {
            $q->where('transactions.transaction_type', 'subscription')
              ->where('transactions.status', 'completed')
              ->latest()
              ->limit(1);
        }])
        ->whereHas('roles', function($q) {
            $q->where('name', 'Member');
        });

        // Filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('level')) {
            $query->where('current_professional_level', $request->level);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('subscription_status', 'active')
                      ->where('subscription_end_date', '>', now());
            } elseif ($request->status === 'expired') {
                $query->where('subscription_end_date', '<=', now());
            } elseif ($request->status === 'suspended') {
                $query->where('subscription_status', 'suspended');
            }
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        // Statistics
        $stats = $this->getSubscriptionStats();

        // Revenue data
        $revenueData = $this->getRevenueData();

        // Get all packages for subscription creation
        $packages = Package::where('is_active', true)->get();
        
        // Get all users for subscription creation
        $allUsers = User::whereHas('roles', function($q) {
            $q->where('name', 'Member');
        })->select('id', 'name', 'email')->get();

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'stats' => $stats,
            'revenueData' => $revenueData,
            'filters' => $request->only(['search', 'level', 'status']),
            'levels' => ['associate', 'professional', 'senior', 'manager', 'director', 'executive', 'ambassador'],
            'packages' => $packages,
            'allUsers' => $allUsers,
        ]);
    }

    public function show(User $user)
    {
        $user->load([
            'currentSubscription.package',
            'subscriptions.package',
            'transactions' => function($q) {
                $q->where('transaction_type', 'subscription')
                  ->orWhere('transaction_type', 'upgrade')
                  ->latest();
            },
            'referralCommissions' => function($q) {
                $q->latest()->limit(10);
            }
        ]);

        $subscriptionHistory = $user->subscriptions()
            ->with('package')
            ->orderBy('created_at', 'desc')
            ->get();

        $paymentHistory = $user->transactions()
            ->where('transaction_type', 'subscription')
            ->orWhere('transaction_type', 'upgrade')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('Admin/Subscriptions/Show', [
            'user' => $user,
            'subscriptionHistory' => $subscriptionHistory,
            'paymentHistory' => $paymentHistory,
        ]);
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,cancelled',
            'reason' => 'nullable|string|max:500',
        ]);

        $user->update([
            'subscription_status' => $request->status,
        ]);

        // Log the action
        activity()
            ->performedOn($user)
            ->withProperties([
                'status' => $request->status,
                'reason' => $request->reason,
                'admin_id' => auth()->id(),
            ])
            ->log('Subscription status updated by admin');

        return back()->with('success', 'Subscription status updated successfully');
    }

    public function extendSubscription(Request $request, User $user)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'reason' => 'nullable|string|max:500',
        ]);

        $currentExpiry = $user->subscription_end_date ?? now();
        $newExpiry = Carbon::parse($currentExpiry)->addDays($request->days);

        $user->update([
            'subscription_end_date' => $newExpiry,
        ]);

        // Log the action
        activity()
            ->performedOn($user)
            ->withProperties([
                'days_added' => $request->days,
                'new_expiry' => $newExpiry,
                'reason' => $request->reason,
                'admin_id' => auth()->id(),
            ])
            ->log('Subscription extended by admin');

        return back()->with('success', "Subscription extended by {$request->days} days");
    }

    public function forceUpgrade(Request $request, User $user)
    {
        $request->validate([
            'level' => 'required|in:associate,professional,senior,manager,director,executive,ambassador',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldLevel = $user->current_professional_level;
        
        $user->update([
            'current_professional_level' => $request->level,
        ]);

        // Log the action
        activity()
            ->performedOn($user)
            ->withProperties([
                'old_level' => $oldLevel,
                'new_level' => $request->level,
                'reason' => $request->reason,
                'admin_id' => auth()->id(),
            ])
            ->log('Professional level upgraded by admin');

        return back()->with('success', "User upgraded from {$oldLevel} to {$request->level}");
    }

    public function createSubscription(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'duration_months' => 'required|integer|min:1|max:12',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($request->user_id);
        $package = Package::findOrFail($request->package_id);

        // Calculate expiry date
        $expiresAt = now()->addMonths($request->duration_months);

        // Create subscription record
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => $package->price * $request->duration_months,
            'billing_cycle' => $request->duration_months == 1 ? 'monthly' : 'annual',
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        // Update user subscription fields
        $user->update([
            'subscription_status' => 'active',
            'subscription_expires_at' => $expiresAt,
            'current_professional_level' => $package->professional_level ?? $user->current_professional_level,
        ]);

        // Create transaction record
        Transaction::create([
            'user_id' => $user->id,
            'amount' => $package->price * $request->duration_months,
            'transaction_type' => 'subscription',
            'status' => 'completed',
            'payment_method' => 'admin_assigned',
            'reference_number' => 'ADMIN-SUB-' . time() . '-' . $user->id,
            'description' => 'Subscription assigned by admin: ' . ($request->reason ?? 'Manual assignment'),
        ]);

        // Log the action
        activity()
            ->performedOn($user)
            ->withProperties([
                'package_id' => $package->id,
                'package_name' => $package->name,
                'duration_months' => $request->duration_months,
                'expires_at' => $expiresAt,
                'reason' => $request->reason,
                'admin_id' => auth()->id(),
            ])
            ->log('Subscription created by admin');

        return back()->with('success', "Subscription created successfully for {$user->name}");
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:suspend,activate,extend',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'days' => 'required_if:action,extend|integer|min:1|max:365',
            'reason' => 'nullable|string|max:500',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $count = 0;

        foreach ($users as $user) {
            switch ($request->action) {
                case 'suspend':
                    $user->update(['subscription_status' => 'suspended']);
                    $count++;
                    break;
                case 'activate':
                    $user->update(['subscription_status' => 'active']);
                    $count++;
                    break;
                case 'extend':
                    $currentExpiry = $user->subscription_end_date ?? now();
                    $newExpiry = Carbon::parse($currentExpiry)->addDays($request->days);
                    $user->update(['subscription_end_date' => $newExpiry]);
                    $count++;
                    break;
            }
        }

        return back()->with('success', "Bulk action completed for {$count} users");
    }

    private function getSubscriptionStats()
    {
        $totalSubscriptions = User::whereHas('roles', function($q) {
            $q->where('name', 'Member');
        })->count();

        $activeSubscriptions = User::where('subscription_status', 'active')
            ->where('subscription_end_date', '>', now())
            ->count();

        $expiredSubscriptions = User::where('subscription_end_date', '<=', now())
            ->count();

        $suspendedSubscriptions = User::where('subscription_status', 'suspended')
            ->count();

        $expiringThisWeek = User::where('subscription_status', 'active')
            ->whereBetween('subscription_end_date', [now(), now()->addWeek()])
            ->count();

        $levelDistribution = User::whereHas('roles', function($q) {
                $q->where('name', 'Member');
            })
            ->select('current_professional_level', DB::raw('count(*) as count'))
            ->groupBy('current_professional_level')
            ->pluck('count', 'current_professional_level')
            ->toArray();

        return [
            'total' => $totalSubscriptions,
            'active' => $activeSubscriptions,
            'expired' => $expiredSubscriptions,
            'suspended' => $suspendedSubscriptions,
            'expiring_this_week' => $expiringThisWeek,
            'level_distribution' => $levelDistribution,
        ];
    }

    private function getRevenueData()
    {
        $monthlyRevenue = Transaction::where('transactions.transaction_type', 'subscription')
            ->where('transactions.status', 'completed')
            ->where('transactions.created_at', '>=', now()->subMonths(12))
            ->selectRaw('YEAR(transactions.created_at) as year, MONTH(transactions.created_at) as month, SUM(transactions.amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'month' => Carbon::create($item->year, $item->month)->format('M Y'),
                    'revenue' => $item->total,
                ];
            });

        $thisMonthRevenue = Transaction::where('transactions.transaction_type', 'subscription')
            ->where('transactions.status', 'completed')
            ->whereMonth('transactions.created_at', now()->month)
            ->whereYear('transactions.created_at', now()->year)
            ->sum('transactions.amount');

        $lastMonthRevenue = Transaction::where('transactions.transaction_type', 'subscription')
            ->where('transactions.status', 'completed')
            ->whereMonth('transactions.created_at', now()->subMonth()->month)
            ->whereYear('transactions.created_at', now()->subMonth()->year)
            ->sum('transactions.amount');

        $revenueByLevel = Transaction::where('transactions.transaction_type', 'subscription')
            ->where('transactions.status', 'completed')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->selectRaw('users.current_professional_level, SUM(transactions.amount) as total')
            ->groupBy('users.current_professional_level')
            ->pluck('total', 'current_professional_level')
            ->toArray();

        return [
            'monthly_chart' => $monthlyRevenue,
            'this_month' => $thisMonthRevenue,
            'last_month' => $lastMonthRevenue,
            'growth_rate' => $lastMonthRevenue > 0 ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0,
            'by_level' => $revenueByLevel,
        ];
    }
}
