<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Domain\Module\Services\UsageLimitService;
use App\Domain\Wallet\Services\UnifiedWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    private const MODULE_ID = 'bizboost';

    public function __construct(
        private SubscriptionService $subscriptionService,
        private TierConfigurationService $tierConfigService,
        private UsageLimitService $usageLimitService,
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

        return Inertia::render('BizBoost/Settings/Subscription', [
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
                'description' => "BizBoost {$request->input('tier')} subscription",
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

            DB::commit();

            return redirect()->route('bizboost.settings.subscription')
                ->with('success', 'Subscription activated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    public function upgrade(Request $request)
    {
        $user = $request->user();
        $currentTier = $this->subscriptionService->getUserTier($user, self::MODULE_ID);
        $tiers = $this->tierConfigService->getAllTiersForDisplay(self::MODULE_ID);
        $usageSummary = $this->usageLimitService->getUsageSummary($user, self::MODULE_ID);

        return Inertia::render('BizBoost/Upgrade', [
            'currentTier' => $currentTier,
            'tiers' => $tiers,
            'usageSummary' => $usageSummary,
            'moduleId' => self::MODULE_ID,
        ]);
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'tier' => 'required|string|in:basic,professional,business',
            'billing_cycle' => 'required|string|in:monthly,annual',
        ]);

        $user = $request->user();
        $currentTier = $this->subscriptionService->getUserTier($user, self::MODULE_ID);
        
        // Can't downgrade through checkout
        $tierOrder = ['free' => 0, 'basic' => 1, 'professional' => 2, 'business' => 3];
        if ($tierOrder[$validated['tier']] <= $tierOrder[$currentTier]) {
            return back()->with('error', 'Please contact support to change your plan.');
        }

        $tierConfig = $this->tierConfigService->getTierConfig(self::MODULE_ID, $validated['tier']);
        $price = $validated['billing_cycle'] === 'annual' 
            ? $tierConfig['price_annual'] 
            : $tierConfig['price_monthly'];

        return Inertia::render('BizBoost/Checkout', [
            'selectedTier' => $validated['tier'],
            'tierConfig' => $tierConfig,
            'billingCycle' => $validated['billing_cycle'],
            'price' => $price,
            'currentTier' => $currentTier,
        ]);
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'tier' => 'required|string|in:basic,professional,business',
            'billing_cycle' => 'required|string|in:monthly,annual',
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string',
        ]);

        $user = $request->user();

        // In production, integrate with payment gateway here
        // For now, we'll simulate successful payment

        try {
            // Update subscription (this would normally happen after payment confirmation)
            // Using the centralized ModuleSubscriptionService
            $moduleSubscriptionService = app(\App\Domain\Module\Services\ModuleSubscriptionService::class);
            
            $moduleSubscriptionService->subscribe(
                $user,
                self::MODULE_ID,
                $validated['tier'],
                $validated['billing_cycle']
            );

            return redirect()->route('bizboost.dashboard')
                ->with('success', "Welcome to BizBoost {$validated['tier']}! Your subscription is now active.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process subscription. Please try again.');
        }
    }

    public function usage(Request $request)
    {
        $user = $request->user();
        $usageSummary = $this->usageLimitService->getUsageSummary($user, self::MODULE_ID);
        $currentTier = $this->subscriptionService->getUserTier($user, self::MODULE_ID);
        $limits = $this->subscriptionService->getUserLimits($user, self::MODULE_ID);

        return Inertia::render('BizBoost/Usage', [
            'usageSummary' => $usageSummary,
            'currentTier' => $currentTier,
            'limits' => $limits,
        ]);
    }

    public function cancel(Request $request)
    {
        $user = $request->user();

        try {
            $moduleSubscriptionService = app(\App\Domain\Module\Services\ModuleSubscriptionService::class);
            $moduleSubscriptionService->cancel($user, self::MODULE_ID);

            return redirect()->route('bizboost.dashboard')
                ->with('success', 'Your subscription has been cancelled. You will retain access until the end of your billing period.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel subscription. Please contact support.');
        }
    }
}
