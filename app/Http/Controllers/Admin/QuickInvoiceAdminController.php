<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuickInvoice\AdminSetting;
use App\Models\QuickInvoice\SubscriptionTier;
use App\Models\QuickInvoice\UsageTracking;
use App\Models\QuickInvoice\UserSubscription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuickInvoiceAdminController extends Controller
{
    public function dashboard(): Response
    {
        // Get usage stats for different periods
        $today = now()->format('Y-m-d');
        $weekAgo = now()->subWeek()->format('Y-m-d');
        $monthAgo = now()->subMonth()->format('Y-m-d');
        
        $todayStats = UsageTracking::getStats($today, $today);
        $weekStats = UsageTracking::getStats($weekAgo, $today);
        $monthStats = UsageTracking::getStats($monthAgo, $today);
        
        // Get subscription stats
        $subscriptionStats = [
            'total_users' => UserSubscription::distinct('user_id')->count(),
            'active_subscriptions' => UserSubscription::where('is_active', true)->count(),
            'by_tier' => UserSubscription::join('quick_invoice_subscription_tiers', 'tier_id', '=', 'quick_invoice_subscription_tiers.id')
                ->where('is_active', true)
                ->groupBy('quick_invoice_subscription_tiers.name')
                ->selectRaw('quick_invoice_subscription_tiers.name, COUNT(*) as count')
                ->pluck('count', 'name')
                ->toArray(),
        ];
        
        // Get recent activity
        $recentActivity = UsageTracking::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user_name' => $activity->user?->name ?? 'Guest',
                    'document_type' => $activity->document_type,
                    'template_used' => $activity->template_used,
                    'integration_source' => $activity->integration_source,
                    'created_at' => $activity->created_at->format('M j, Y H:i'),
                ];
            });
        
        // Get monetization settings
        $monetizationSettings = AdminSetting::getMonetizationSettings();
        
        return Inertia::render('Admin/QuickInvoice/Dashboard', [
            'stats' => [
                'today' => $todayStats,
                'week' => $weekStats,
                'month' => $monthStats,
            ],
            'subscriptionStats' => $subscriptionStats,
            'recentActivity' => $recentActivity,
            'monetizationSettings' => $monetizationSettings,
            'tiers' => SubscriptionTier::where('is_active', true)->get(),
        ]);
    }
    
    public function updateMonetizationSettings(Request $request)
    {
        $request->validate([
            'usage_limits_enabled' => 'required|boolean',
            'free_tier_limit' => 'required|integer|min:0|max:1000',
            'require_subscription' => 'required|boolean',
            'grace_period_days' => 'required|integer|min:0|max:30',
        ]);
        
        AdminSetting::updateMonetizationSettings($request->all(), auth()->id());
        
        // If enabling limits, update the free tier
        if ($request->usage_limits_enabled) {
            $freeTier = SubscriptionTier::where('name', 'Free')->first();
            if ($freeTier) {
                $freeTier->update([
                    'documents_per_month' => $request->free_tier_limit,
                ]);
            }
        }
        
        return back()->with('success', 'Monetization settings updated successfully');
    }
    
    public function toggleUsageLimits(Request $request)
    {
        $enabled = $request->boolean('enabled');
        AdminSetting::setUsageLimitsEnabled($enabled, auth()->id());
        
        $message = $enabled 
            ? 'Usage limits have been enabled. Users will now be restricted based on their subscription tier.'
            : 'Usage limits have been disabled. All users can create unlimited documents.';
            
        return back()->with('success', $message);
    }
    
    public function usageAnalytics(Request $request): Response
    {
        $period = $request->get('period', '30d');
        $days = match($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };
        
        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        
        // Daily usage data for chart
        $dailyUsage = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayStats = UsageTracking::getStats($date, $date);
            
            $dailyUsage->push([
                'date' => $date,
                'documents' => $dayStats['total_documents'],
                'users' => $dayStats['unique_users'],
                'sessions' => $dayStats['unique_sessions'],
            ]);
        }
        
        $overallStats = UsageTracking::getStats($startDate, $endDate);
        
        // Top users
        $topUsers = UsageTracking::whereNotNull('user_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->join('users', 'user_id', '=', 'users.id')
            ->groupBy('user_id', 'users.name', 'users.email')
            ->selectRaw('user_id, users.name, users.email, COUNT(*) as document_count')
            ->orderByDesc('document_count')
            ->limit(10)
            ->get();
        
        return Inertia::render('Admin/QuickInvoice/Analytics', [
            'period' => $period,
            'dailyUsage' => $dailyUsage,
            'overallStats' => $overallStats,
            'topUsers' => $topUsers,
        ]);
    }
    
    public function userManagement(Request $request): Response
    {
        $search = $request->get('search');
        $tierFilter = $request->get('tier');
        
        $query = UserSubscription::with(['user', 'tier'])
            ->where('is_active', true);
            
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($tierFilter) {
            $query->whereHas('tier', function ($q) use ($tierFilter) {
                $q->where('name', $tierFilter);
            });
        }
        
        $subscriptions = $query->paginate(20)->through(function ($subscription) {
            $monthlyUsage = UsageTracking::getUserMonthlyUsage($subscription->user_id);
            
            return [
                'id' => $subscription->id,
                'user' => [
                    'id' => $subscription->user->id,
                    'name' => $subscription->user->name,
                    'email' => $subscription->user->email,
                ],
                'tier' => [
                    'name' => $subscription->tier->name,
                    'documents_per_month' => $subscription->tier->documents_per_month,
                    'formatted_price' => $subscription->tier->formatted_price,
                ],
                'monthly_usage' => $monthlyUsage,
                'remaining_documents' => $subscription->getRemainingDocuments(),
                'usage_percentage' => $subscription->getUsagePercentage(),
                'starts_at' => $subscription->starts_at->format('M j, Y'),
                'expires_at' => $subscription->expires_at?->format('M j, Y'),
            ];
        });
        
        return Inertia::render('Admin/QuickInvoice/UserManagement', [
            'subscriptions' => $subscriptions,
            'tiers' => SubscriptionTier::where('is_active', true)->get(),
            'filters' => [
                'search' => $search,
                'tier' => $tierFilter,
            ],
        ]);
    }
}