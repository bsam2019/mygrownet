<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuickInvoice\AdminSetting;
use App\Models\QuickInvoice\SubscriptionTier;
use App\Models\QuickInvoice\UsageTracking;
use App\Models\QuickInvoice\UserSubscription;
use App\Services\QuickInvoice\QuickInvoiceSubscriptionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuickInvoiceAdminController extends Controller
{
    public function dashboard(): Response
    {
        $today = now()->format('Y-m-d');
        $weekAgo = now()->subWeek()->format('Y-m-d');
        $monthAgo = now()->subMonth()->format('Y-m-d');
        
        $todayStats = UsageTracking::getStats($today, $today);
        $weekStats = UsageTracking::getStats($weekAgo, $today);
        $monthStats = UsageTracking::getStats($monthAgo, $today);
        
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
        
        $monetizationSettings = AdminSetting::getMonetizationSettings();
        $trialSettings = AdminSetting::get('trial_settings', [
            'trial_days' => 30,
            'tier_on_trial' => 'Basic',
            'require_payment_after_trial' => true,
        ]);
        
        $subscriptionService = app(QuickInvoiceSubscriptionService::class);
        $billingStats = $subscriptionService->getAdminStats();
        
        return Inertia::render('Admin/QuickInvoice/Dashboard', [
            'stats' => [
                'today' => $todayStats,
                'week' => $weekStats,
                'month' => $monthStats,
            ],
            'subscriptionStats' => $subscriptionStats,
            'recentActivity' => $recentActivity,
            'monetizationSettings' => $monetizationSettings,
            'trialSettings' => $trialSettings,
            'billingStats' => $billingStats,
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
    
    public function updateTrialSettings(Request $request)
    {
        $request->validate([
            'trial_days' => 'required|integer|min:0|max:365',
            'tier_on_trial' => 'required|string|exists:quick_invoice_subscription_tiers,name',
            'require_payment_after_trial' => 'required|boolean',
        ]);

        AdminSetting::set('trial_settings', [
            'trial_days' => (int) $request->trial_days,
            'tier_on_trial' => $request->tier_on_trial,
            'require_payment_after_trial' => (bool) $request->require_payment_after_trial,
        ], auth()->id());

        return back()->with('success', 'Trial settings updated successfully');
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
            
            $status = 'free';
            if ($subscription->onTrial()) $status = 'trial';
            elseif ($subscription->isPaid()) $status = 'active';
            elseif ($subscription->expires_at && $subscription->expires_at->isPast()) $status = 'expired';

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
                'status' => $status,
                'is_trial' => $subscription->onTrial(),
                'trial_ends_at' => $subscription->trial_ends_at?->format('M j, Y'),
                'is_paid' => $subscription->isPaid(),
                'last_payment_at' => $subscription->last_payment_at?->format('M j, Y'),
                'last_payment_amount' => $subscription->last_payment_amount,
                'payment_method' => $subscription->payment_method,
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

    // ── Tier (Plan) Management ──────────────────────────────────────────────────

    public function tiers(): Response
    {
        $tiers = SubscriptionTier::orderBy('price')->get()->map(function ($tier) {
            return [
                'id' => $tier->id,
                'name' => $tier->name,
                'price' => (float) $tier->price,
                'currency' => $tier->currency,
                'formatted_price' => $tier->formatted_price,
                'documents_per_month' => $tier->documents_per_month,
                'features' => $tier->features,
                'is_active' => $tier->is_active,
                'subscriber_count' => $tier->userSubscriptions()->where('is_active', true)->count(),
                'created_at' => $tier->created_at->format('M j, Y'),
            ];
        });

        return Inertia::render('Admin/QuickInvoice/Tiers/Index', [
            'tiers' => $tiers,
        ]);
    }

    public function createTier(): Response
    {
        return Inertia::render('Admin/QuickInvoice/Tiers/Form', [
            'tier' => null,
        ]);
    }

    public function storeTier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:quick_invoice_subscription_tiers,name',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'documents_per_month' => 'required|integer|min:-1',
            'features' => 'required|array',
            'is_active' => 'boolean',
        ]);

        SubscriptionTier::create([
            'name' => $request->name,
            'price' => $request->price,
            'currency' => $request->currency,
            'documents_per_month' => $request->documents_per_month,
            'features' => $request->features,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.quick-invoice.tiers')
            ->with('success', 'Plan created successfully');
    }

    public function editTier(int $id): Response
    {
        $tier = SubscriptionTier::findOrFail($id);

        return Inertia::render('Admin/QuickInvoice/Tiers/Form', [
            'tier' => [
                'id' => $tier->id,
                'name' => $tier->name,
                'price' => (float) $tier->price,
                'currency' => $tier->currency,
                'documents_per_month' => $tier->documents_per_month,
                'features' => $tier->features,
                'is_active' => $tier->is_active,
            ],
        ]);
    }

    public function updateTier(int $id, Request $request)
    {
        $tier = SubscriptionTier::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:quick_invoice_subscription_tiers,name,' . $id,
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'documents_per_month' => 'required|integer|min:-1',
            'features' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $tier->update([
            'name' => $request->name,
            'price' => $request->price,
            'currency' => $request->currency,
            'documents_per_month' => $request->documents_per_month,
            'features' => $request->features,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.quick-invoice.tiers')
            ->with('success', 'Plan updated successfully');
    }

    public function destroyTier(int $id)
    {
        $tier = SubscriptionTier::findOrFail($id);

        if ($tier->userSubscriptions()->exists()) {
            return back()->with('error', 'Cannot delete a plan that has active subscribers. Deactivate it instead.');
        }

        $tier->delete();

        return redirect()->route('admin.quick-invoice.tiers')
            ->with('success', 'Plan deleted successfully');
    }
}