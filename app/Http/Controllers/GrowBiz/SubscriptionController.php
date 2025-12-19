<?php

namespace App\Http\Controllers\GrowBiz;

use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Domain\Wallet\Services\UnifiedWalletService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    private const MODULE_ID = 'growbiz';

    public function __construct(
        private SubscriptionService $subscriptionService,
        private TierConfigurationService $tierConfigService,
        private UnifiedWalletService $walletService
    ) {}

    /**
     * Show subscription settings page (in-app)
     */
    public function settings(Request $request)
    {
        $user = $request->user();
        $currentTier = $this->subscriptionService->getUserTier($user, self::MODULE_ID);
        
        $subscription = DB::table('module_subscriptions')
            ->where('user_id', $user->id)
            ->where('module_id', self::MODULE_ID)
            ->where('status', 'active')
            ->first();

        // Get dynamic tiers from admin-configured database or config fallback
        $tiers = $this->tierConfigService->getAllTiersForDisplay(self::MODULE_ID);

        return Inertia::render('GrowBiz/Settings/Subscription', [
            'walletBalance' => $this->walletService->calculateBalance($user),
            'currentTier' => $currentTier ?: 'free',
            'tiers' => $tiers,
            'subscription' => $subscription ? [
                'tier' => $subscription->subscription_tier,
                'expires_at' => $subscription->expires_at,
                'auto_renew' => $subscription->auto_renew,
            ] : null,
        ]);
    }

    /**
     * Purchase subscription using wallet balance
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'module_id' => 'required|string',
            'tier' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $user = $request->user();
        $amount = (float) $request->input('amount');

        // Check wallet balance
        $balance = $this->walletService->calculateBalance($user);
        if ($balance < $amount) {
            return back()->with('error', 'Insufficient wallet balance. Please top up your wallet.');
        }

        try {
            DB::beginTransaction();

            // Calculate expiry date
            $expiresAt = $request->input('billing_cycle') === 'yearly'
                ? now()->addYear()
                : now()->addMonth();

            // Create or update subscription
            DB::table('module_subscriptions')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'module_id' => self::MODULE_ID,
                ],
                [
                    'subscription_tier' => $request->input('tier'),
                    'status' => 'active',
                    'started_at' => now(),
                    'expires_at' => $expiresAt,
                    'billing_cycle' => $request->input('billing_cycle'),
                    'amount' => $amount,
                    'currency' => 'ZMW',
                    'auto_renew' => true,
                    'updated_at' => now(),
                ]
            );

            // Record transaction
            DB::table('transactions')->insert([
                'user_id' => $user->id,
                'transaction_type' => 'subscription_payment',
                'amount' => -$amount,
                'status' => 'completed',
                'description' => "GrowBiz {$request->input('tier')} subscription",
                'metadata' => json_encode([
                    'module_id' => self::MODULE_ID,
                    'tier' => $request->input('tier'),
                    'billing_cycle' => $request->input('billing_cycle'),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Clear caches
            $this->walletService->clearCache($user);
            $this->subscriptionService->clearCache($user, self::MODULE_ID);

            DB::commit();

            return redirect()->route('growbiz.settings.subscription')
                ->with('success', 'Subscription activated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Show the upgrade/pricing page
     */
    public function upgrade(Request $request)
    {
        $user = $request->user();
        $currentTier = $this->subscriptionService->getUserTier($user, self::MODULE_ID);
        
        $subscription = DB::table('module_subscriptions')
            ->where('user_id', $user->id)
            ->where('module_id', self::MODULE_ID)
            ->where('status', 'active')
            ->first();

        return Inertia::render('GrowBiz/Upgrade', [
            'currentTier' => $currentTier,
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

        $prices = [
            'basic' => ['monthly' => 79, 'annual' => 758],
            'professional' => ['monthly' => 199, 'annual' => 1910],
            'business' => ['monthly' => 399, 'annual' => 3830],
        ];

        $price = $prices[$tier][$billing] ?? $prices['basic']['monthly'];

        return Inertia::render('GrowBiz/Checkout', [
            'tier' => $tier,
            'billing' => $billing,
            'price' => $price,
            'currency' => 'ZMW',
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

        $prices = [
            'basic' => ['monthly' => 79, 'annual' => 758],
            'professional' => ['monthly' => 199, 'annual' => 1910],
            'business' => ['monthly' => 399, 'annual' => 3830],
        ];

        $amount = $prices[$tier][$billingCycle];

        // Calculate expiry date
        $expiresAt = $billingCycle === 'annual' 
            ? now()->addYear() 
            : now()->addMonth();

        // Create or update subscription
        DB::table('module_subscriptions')->updateOrInsert(
            [
                'user_id' => $user->id,
                'module_id' => self::MODULE_ID,
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
        $this->subscriptionService->clearCache($user, self::MODULE_ID);

        return redirect()->route('growbiz.dashboard')
            ->with('success', "Successfully upgraded to {$tier} plan!");
    }

    /**
     * Get current usage stats (API endpoint)
     */
    public function usage(Request $request)
    {
        $user = $request->user();
        $summary = $this->subscriptionService->getUsageSummary($user, self::MODULE_ID);

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
            ->where('module_id', self::MODULE_ID)
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'auto_renew' => false,
                'updated_at' => now(),
            ]);

        return redirect()->route('growbiz.upgrade')
            ->with('success', 'Your subscription has been cancelled. You\'ll retain access until the end of your billing period.');
    }
}
