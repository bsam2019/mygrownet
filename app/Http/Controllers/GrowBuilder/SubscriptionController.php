<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Domain\Module\Services\SubscriptionService;
use App\Domain\Module\Services\TierConfigurationService;
use App\Domain\Wallet\Services\UnifiedWalletService;
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
        private UnifiedWalletService $walletService,
        private StorageService $storageService
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
     * Purchase subscription using wallet balance
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'tier' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $user = $request->user();
        $amount = (float) $request->input('amount');
        $tier = $request->input('tier');

        // Free tier doesn't need payment
        if ($amount > 0) {
            $balance = $this->walletService->calculateBalance($user);
            if ($balance < $amount) {
                return back()->with('error', 'Insufficient wallet balance. Please top up your wallet.');
            }
        }

        try {
            DB::beginTransaction();

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
                    'subscription_tier' => $tier,
                    'status' => 'active',
                    'started_at' => now(),
                    'expires_at' => $expiresAt,
                    'billing_cycle' => $request->input('billing_cycle'),
                    'amount' => $amount,
                    'currency' => 'ZMW',
                    'auto_renew' => $amount > 0,
                    'updated_at' => now(),
                ]
            );

            // Record transaction if paid
            if ($amount > 0) {
                DB::table('transactions')->insert([
                    'user_id' => $user->id,
                    'transaction_type' => 'subscription_payment',
                    'amount' => -$amount,
                    'status' => 'completed',
                    'description' => "GrowBuilder {$tier} subscription",
                    'metadata' => json_encode([
                        'module_id' => self::MODULE_ID,
                        'tier' => $tier,
                        'billing_cycle' => $request->input('billing_cycle'),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->walletService->clearCache($user);
            }

            $this->subscriptionService->clearCache($user, self::MODULE_ID);

            DB::commit();

            return redirect()->route('growbuilder.subscription.index')
                ->with('success', 'Subscription activated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred. Please try again.');
        }
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
