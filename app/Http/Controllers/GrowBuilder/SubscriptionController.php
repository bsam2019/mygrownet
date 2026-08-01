<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Domain\GrowNet\Wallet\Services\WalletService;
use App\Http\Controllers\Controller;
use App\Services\GrowBuilder\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    private const MODULE_ID = 'growbuilder';

    public function __construct(
        private SubscriptionService $subscriptionService,
        private TierConfigurationService $tierConfigService,
        private WalletService $walletService,
        private StorageService $storageService,
    ) {}

    /**
     * Show subscription/pricing page
     */
    public function index(Request $request)
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

        // Get user's site count for usage display
        $siteCount = DB::table('growbuilder_sites')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'deleted')
            ->count();

        // Check if user is an active MyGrowNet member (for member benefit)
        $isMember = $user->account_type === 'member' && $user->subscription_status === 'active';

        // Get storage usage for current user
        $totalStorageUsed = DB::table('growbuilder_sites')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'deleted')
            ->sum('storage_used');

        $currentTierStorageLimit = $this->storageService->getStorageLimitForTier($currentTier ?: 'free');

        // Get storage limits for all tiers
        $tierStorageLimits = $this->storageService->getAllTierLimits();

        return Inertia::render('GrowBuilder/Settings/Subscription', [
            'walletBalance' => $this->walletService->calculateBalance($user),
            'currentTier' => $currentTier ?: 'free',
            'tiers' => $tiers,
            'subscription' => $subscription ? [
                'tier' => $subscription->subscription_tier,
                'expires_at' => $subscription->expires_at,
                'auto_renew' => $subscription->auto_renew,
                'billing_cycle' => $subscription->billing_cycle,
            ] : null,
            'usage' => [
                'sites' => $siteCount,
                'storage_used' => $totalStorageUsed,
                'storage_used_formatted' => $this->storageService->formatBytes($totalStorageUsed),
                'storage_limit' => $currentTierStorageLimit,
                'storage_limit_formatted' => $this->storageService->formatBytes($currentTierStorageLimit),
                'storage_percentage' => $currentTierStorageLimit > 0 
                    ? min(100, round(($totalStorageUsed / $currentTierStorageLimit) * 100, 1)) 
                    : 0,
            ],
            'tierStorageLimits' => $tierStorageLimits,
            'isMember' => $isMember,
        ]);
    }

    /**
     * Purchase subscription — routes through the unified PawaPay checkout.
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'tier' => 'required|string',
            'billing_cycle' => 'required|in:monthly,yearly,annual',
        ]);

        $user = $request->user();
        $tier = $request->input('tier');
        $billingCycle = $request->input('billing_cycle') === 'yearly'
            ? 'annual'
            : $request->input('billing_cycle');

        $currentTier = $this->subscriptionService->getUserTier($user, self::MODULE_ID);
        $tierOrder = ['free' => 0, 'starter' => 1, 'business' => 2, 'agency' => 3];
        $tierOrder[$tier] = $tierOrder[$tier] ?? 0;

        // Can't downgrade through checkout
        if (isset($tierOrder[$currentTier]) && $tierOrder[$tier] <= $tierOrder[$currentTier]) {
            return back()->with('error', 'Please contact support to change your plan.');
        }

        \Log::info('GrowBuilder subscription checkout', [
            'user_id' => $user->id,
            'tier' => $tier,
            'billing_cycle' => $billingCycle,
        ]);

        return redirect()->route('subscriptions.checkout', [
            'module' => self::MODULE_ID,
            'tier' => $tier,
            'billing_cycle' => $billingCycle,
            'return_url' => route('growbuilder.subscription.index'),
        ]);
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

        $this->subscriptionService->clearCache($user, self::MODULE_ID);

        return back()->with('success', 'Subscription cancelled. Access continues until billing period ends.');
    }
}
