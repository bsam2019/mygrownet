<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StarterKitPurchase;
use App\Models\StarterKitContentAccess;
use App\Models\MemberAchievement;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StarterKitAdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_purchases' => StarterKitPurchase::count(),
            'total_revenue' => StarterKitPurchase::where('status', 'completed')->sum('amount'),
            'pending_purchases' => StarterKitPurchase::where('status', 'pending')->count(),
            'active_members' => User::where('has_starter_kit', true)->count(),
            'completion_rate' => $this->getCompletionRate(),
            'avg_progress' => $this->getAverageProgress(),
        ];

        $recentPurchases = StarterKitPurchase::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($purchase) => [
                'id' => $purchase->id,
                'invoice_number' => $purchase->invoice_number,
                'user_name' => $purchase->user->name,
                'user_email' => $purchase->user->email,
                'amount' => $purchase->amount,
                'status' => $purchase->status,
                'payment_method' => $purchase->payment_method,
                'purchased_at' => $purchase->created_at->format('Y-m-d H:i'),
            ]);

        $monthlyRevenue = $this->getMonthlyRevenue();
        $contentAccessStats = $this->getContentAccessStats();

        return Inertia::render('Admin/StarterKit/Dashboard', [
            'stats' => $stats,
            'recentPurchases' => $recentPurchases,
            'monthlyRevenue' => $monthlyRevenue,
            'contentAccessStats' => $contentAccessStats,
        ]);
    }

    public function purchases(Request $request)
    {
        $query = StarterKitPurchase::with('user');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('invoice_number', 'like', "%{$request->search}%");
        }

        $purchases = $query->latest()
            ->paginate(20)
            ->through(fn($purchase) => [
                'id' => $purchase->id,
                'invoice_number' => $purchase->invoice_number,
                'user' => [
                    'id' => $purchase->user->id,
                    'name' => $purchase->user->name,
                    'email' => $purchase->user->email,
                ],
                'amount' => $purchase->amount,
                'status' => $purchase->status,
                'payment_method' => $purchase->payment_method,
                'payment_reference' => $purchase->payment_reference,
                'purchased_at' => $purchase->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Admin/StarterKit/Purchases', [
            'purchases' => $purchases,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function updatePurchaseStatus(Request $request, StarterKitPurchase $purchase)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,failed,refunded',
        ]);

        $purchase->update(['status' => $request->status]);

        if ($request->status === 'completed') {
            $purchase->user->update(['has_starter_kit' => true]);
        } elseif ($request->status === 'refunded') {
            $purchase->user->update(['has_starter_kit' => false]);
        }

        return back()->with('success', 'Purchase status updated successfully.');
    }

    public function members(Request $request)
    {
        $query = User::where('has_starter_kit', true)
            ->with(['starterKitPurchases', 'achievements']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $members = $query->latest('updated_at')
            ->paginate(20)
            ->through(function($user) {
                $purchase = $user->starterKitPurchases()->latest()->first();
                $progress = $this->calculateUserProgress($user);
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'purchased_at' => $purchase?->created_at->format('Y-m-d H:i'),
                    'progress' => $progress,
                    'achievements_count' => $user->achievements()->where('category', 'starter_kit')->count(),
                    'last_access' => StarterKitContentAccess::where('user_id', $user->id)
                        ->latest()
                        ->first()?->accessed_at->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('Admin/StarterKit/Members', [
            'members' => $members,
            'filters' => $request->only(['search']),
        ]);
    }

    public function analytics()
    {
        $data = [
            'purchaseTrends' => $this->getPurchaseTrends(),
            'revenueTrends' => $this->getRevenueTrends(),
            'contentEngagement' => $this->getContentEngagement(),
            'achievementStats' => $this->getAchievementStats(),
            'paymentMethods' => $this->getPaymentMethodStats(),
        ];

        return Inertia::render('Admin/StarterKit/Analytics', $data);
    }

    private function getCompletionRate(): float
    {
        $totalMembers = User::where('has_starter_kit', true)->count();
        if ($totalMembers === 0) return 0;

        // Count members who have unlocked all content (13 items)
        $completed = \DB::table('users')
            ->where('has_starter_kit', true)
            ->whereExists(function($query) {
                $query->select(\DB::raw(1))
                    ->from('starter_kit_unlocks')
                    ->whereColumn('starter_kit_unlocks.user_id', 'users.id')
                    ->where('is_unlocked', true)
                    ->havingRaw('COUNT(*) >= 13');
            })
            ->count();

        return round(($completed / $totalMembers) * 100, 1);
    }

    private function getAverageProgress(): float
    {
        $users = User::where('has_starter_kit', true)->get();
        if ($users->isEmpty()) return 0;

        $totalProgress = $users->sum(fn($user) => $this->calculateUserProgress($user));
        return round($totalProgress / $users->count(), 1);
    }

    private function calculateUserProgress(User $user): float
    {
        $totalItems = 13; // All unlockable items
        $accessedItems = StarterKitContentAccess::where('user_id', $user->id)
            ->where('completion_status', 'completed')
            ->count();

        return round(($accessedItems / $totalItems) * 100, 1);
    }

    private function getMonthlyRevenue(): array
    {
        return StarterKitPurchase::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as revenue, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($item) => [
                'month' => $item->month,
                'revenue' => $item->revenue,
                'count' => $item->count,
            ])
            ->toArray();
    }

    private function getContentAccessStats(): array
    {
        return StarterKitContentAccess::selectRaw('content_type, COUNT(DISTINCT user_id) as users, COUNT(*) as total_accesses')
            ->groupBy('content_type')
            ->get()
            ->map(fn($item) => [
                'type' => $item->content_type,
                'users' => $item->users,
                'accesses' => $item->total_accesses,
            ])
            ->toArray();
    }

    private function getPurchaseTrends(): array
    {
        return StarterKitPurchase::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getRevenueTrends(): array
    {
        return StarterKitPurchase::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getContentEngagement(): array
    {
        return StarterKitContentAccess::selectRaw('content_type, content_id, COUNT(*) as views, AVG(time_spent) as avg_time')
            ->groupBy('content_type', 'content_id')
            ->orderByDesc('views')
            ->take(10)
            ->get()
            ->toArray();
    }

    private function getAchievementStats(): array
    {
        return MemberAchievement::where('category', 'starter_kit')
            ->selectRaw('achievement_name, COUNT(*) as count')
            ->groupBy('achievement_name')
            ->get()
            ->map(fn($item) => [
                'achievement_type' => $item->achievement_name,
                'count' => $item->count,
            ])
            ->toArray();
    }

    private function getPaymentMethodStats(): array
    {
        return StarterKitPurchase::where('status', 'completed')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as revenue')
            ->groupBy('payment_method')
            ->get()
            ->toArray();
    }
}
