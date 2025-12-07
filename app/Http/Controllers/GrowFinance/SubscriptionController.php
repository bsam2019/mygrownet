<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    /**
     * Show the upgrade/pricing page
     */
    public function upgrade(Request $request)
    {
        $user = $request->user();
        $currentTier = $this->subscriptionService->getUserTier($user);
        
        $subscription = DB::table('module_subscriptions')
            ->where('user_id', $user->id)
            ->where('module_id', 'growfinance')
            ->where('status', 'active')
            ->first();

        // Get tier configuration from config file
        $tiersConfig = config('modules.growfinance.tiers', []);
        $tiers = [];
        
        foreach ($tiersConfig as $key => $tierData) {
            $tiers[$key] = [
                'key' => $key,
                'name' => $tierData['name'] ?? ucfirst($key),
                'description' => $tierData['description'] ?? '',
                'price_monthly' => $tierData['price_monthly'] ?? 0,
                'price_annual' => $tierData['price_annual'] ?? 0,
                'popular' => $tierData['popular'] ?? false,
                'limits' => $tierData['limits'] ?? [],
                'features' => $tierData['features'] ?? [],
                'reports' => $tierData['reports'] ?? [],
            ];
        }

        return Inertia::render('GrowFinance/Upgrade', [
            'currentTier' => $currentTier,
            'tiers' => $tiers,
            'subscription' => $subscription ? [
                'expires_at' => $subscription->expires_at,
                'billing_cycle' => $subscription->billing_cycle,
                'auto_renew' => $subscription->auto_renew,
            ] : null,
        ]);
    }

    /**
     * Show checkout page for selected plan
     */
    public function checkout(Request $request)
    {
        $tier = $request->query('tier', 'basic');
        $billing = $request->query('billing', 'monthly');

        // Get prices from config
        $tierConfig = config("modules.growfinance.tiers.{$tier}");
        
        if (!$tierConfig) {
            return redirect()->route('growfinance.upgrade')
                ->with('error', 'Invalid plan selected.');
        }

        $price = $billing === 'annual' 
            ? ($tierConfig['price_annual'] ?? 0)
            : ($tierConfig['price_monthly'] ?? 0);

        return Inertia::render('GrowFinance/Checkout', [
            'tier' => $tier,
            'tierName' => $tierConfig['name'] ?? ucfirst($tier),
            'billing' => $billing,
            'price' => $price,
            'currency' => 'ZMW',
            'features' => $tierConfig['features'] ?? [],
            'limits' => $tierConfig['limits'] ?? [],
        ]);
    }

    /**
     * Process subscription payment
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'tier' => 'required|in:basic,professional,business',
            'billing_cycle' => 'required|in:monthly,annual',
            'payment_method' => 'required|in:mtn_momo,airtel_money,bank_transfer',
            'phone_number' => 'required_if:payment_method,mtn_momo,airtel_money',
        ]);

        $user = $request->user();
        $tier = $request->input('tier');
        $billingCycle = $request->input('billing_cycle');

        // Get price from config
        $tierConfig = config("modules.growfinance.tiers.{$tier}");
        
        if (!$tierConfig) {
            return back()->with('error', 'Invalid plan selected.');
        }

        $amount = $billingCycle === 'annual' 
            ? ($tierConfig['price_annual'] ?? 0)
            : ($tierConfig['price_monthly'] ?? 0);

        // Calculate expiry date
        $expiresAt = $billingCycle === 'annual' 
            ? now()->addYear() 
            : now()->addMonth();

        // Create or update subscription
        DB::table('module_subscriptions')->updateOrInsert(
            [
                'user_id' => $user->id,
                'module_id' => 'growfinance',
            ],
            [
                'subscription_tier' => $tier,
                'status' => 'active',
                'started_at' => now(),
                'expires_at' => $expiresAt,
                'billing_cycle' => $billingCycle,
                'amount' => $amount,
                'currency' => 'ZMW',
                'auto_renew' => true,
                'updated_at' => now(),
            ]
        );

        // Clear subscription cache
        $this->subscriptionService->clearUsageCache($user);

        return redirect()->route('growfinance.dashboard')
            ->with('success', "Successfully upgraded to {$tier} plan!");
    }

    /**
     * Get current usage stats (API endpoint)
     */
    public function usage(Request $request)
    {
        $user = $request->user();
        $summary = $this->subscriptionService->getUsageSummary($user);

        return response()->json($summary);
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $user = $request->user();

        DB::table('module_subscriptions')
            ->where('user_id', $user->id)
            ->where('module_id', 'growfinance')
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'auto_renew' => false,
                'updated_at' => now(),
            ]);

        return redirect()->route('growfinance.upgrade')
            ->with('success', 'Your subscription has been cancelled. You\'ll retain access until the end of your billing period.');
    }
}
