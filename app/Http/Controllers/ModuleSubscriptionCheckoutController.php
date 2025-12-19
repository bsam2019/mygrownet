<?php

namespace App\Http\Controllers;

use App\Domain\Module\Services\ModuleSubscriptionService;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Domain\Module\ValueObjects\SubscriptionTier;
use App\Domain\Module\ValueObjects\Money;
use App\Domain\Wallet\Services\UnifiedWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Module Subscription Checkout Controller
 * 
 * Handles subscription purchases using wallet balance
 */
class ModuleSubscriptionCheckoutController extends Controller
{
    public function __construct(
        private readonly ModuleSubscriptionService $subscriptionService,
        private readonly UnifiedWalletService $walletService
    ) {}

    /**
     * Process subscription purchase
     */
    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|string',
            'tier' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $user = auth()->user();
        $amount = (float) $validated['amount'];

        // Check wallet balance
        $balance = $this->walletService->calculateBalance($user);
        
        if ($balance < $amount) {
            return back()->with('error', 'Insufficient wallet balance. Please top up your wallet.');
        }

        try {
            DB::beginTransaction();

            // Create subscription
            $subscription = $this->subscriptionService->subscribe(
                userId: $user->id,
                moduleId: ModuleId::fromString($validated['module_id']),
                tier: SubscriptionTier::fromString($validated['tier']),
                amount: Money::fromAmount($amount),
                billingCycle: $validated['billing_cycle']
            );

            // Record transaction
            DB::table('transactions')->insert([
                'user_id' => $user->id,
                'transaction_type' => 'subscription_payment',
                'amount' => -$amount, // Negative for debit
                'status' => 'completed',
                'description' => "Subscription: {$validated['module_id']} - {$validated['tier']} tier",
                'metadata' => json_encode([
                    'module_id' => $validated['module_id'],
                    'tier' => $validated['tier'],
                    'billing_cycle' => $validated['billing_cycle'],
                    'subscription_id' => $subscription->getId()->value(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Clear wallet cache
            $this->walletService->clearCache($user);

            DB::commit();

            // Create notification
            $this->createSubscriptionNotification($user->id, $validated['module_id'], $validated['tier']);

            return redirect()
                ->route('lifeplus.profile.subscription')
                ->with('success', 'Subscription activated successfully!');

        } catch (\DomainException $e) {
            DB::rollBack();
            Log::error('Subscription purchase failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Subscription purchase error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', 'An error occurred while processing your subscription. Please try again.');
        }
    }

    /**
     * Start free trial
     */
    public function startTrial(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|string',
            'tier' => 'required|string',
            'trial_days' => 'integer|min:1|max:30',
        ]);

        $user = auth()->user();

        try {
            $subscription = $this->subscriptionService->startTrial(
                userId: $user->id,
                moduleId: ModuleId::fromString($validated['module_id']),
                tier: SubscriptionTier::fromString($validated['tier']),
                trialDays: $validated['trial_days'] ?? 14
            );

            // Create notification
            $this->createTrialNotification($user->id, $validated['module_id'], $validated['trial_days'] ?? 14);

            return redirect()
                ->route('lifeplus.profile.subscription')
                ->with('success', 'Free trial started successfully!');

        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Upgrade subscription
     */
    public function upgrade(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|string',
            'new_tier' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();
        $amount = (float) $validated['amount'];

        // Check wallet balance
        $balance = $this->walletService->calculateBalance($user);
        
        if ($balance < $amount) {
            return back()->with('error', 'Insufficient wallet balance. Please top up your wallet.');
        }

        try {
            DB::beginTransaction();

            // Upgrade subscription
            $subscription = $this->subscriptionService->upgrade(
                userId: $user->id,
                moduleId: ModuleId::fromString($validated['module_id']),
                newTier: SubscriptionTier::fromString($validated['new_tier']),
                newAmount: Money::fromAmount($amount)
            );

            // Record transaction
            DB::table('transactions')->insert([
                'user_id' => $user->id,
                'transaction_type' => 'subscription_upgrade',
                'amount' => -$amount,
                'status' => 'completed',
                'description' => "Upgrade: {$validated['module_id']} to {$validated['new_tier']} tier",
                'metadata' => json_encode([
                    'module_id' => $validated['module_id'],
                    'new_tier' => $validated['new_tier'],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Clear wallet cache
            $this->walletService->clearCache($user);

            DB::commit();

            // Create notification
            $this->createUpgradeNotification($user->id, $validated['module_id'], $validated['new_tier']);

            return redirect()
                ->route('lifeplus.profile.subscription')
                ->with('success', 'Subscription upgraded successfully!');

        } catch (\DomainException $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'module_id' => 'required|string',
        ]);

        $user = auth()->user();

        try {
            $this->subscriptionService->cancel(
                userId: $user->id,
                moduleId: ModuleId::fromString($validated['module_id'])
            );

            return redirect()
                ->route('lifeplus.profile.subscription')
                ->with('success', 'Subscription cancelled successfully.');

        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create subscription notification
     */
    private function createSubscriptionNotification(int $userId, string $moduleId, string $tier): void
    {
        DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $userId,
            'type' => 'App\\Notifications\\SubscriptionActivated',
            'module' => 'core',
            'category' => 'success',
            'title' => 'Subscription Activated',
            'message' => "Your {$tier} subscription for {$moduleId} is now active!",
            'priority' => 'normal',
            'created_at' => now(),
        ]);
    }

    /**
     * Create trial notification
     */
    private function createTrialNotification(int $userId, string $moduleId, int $days): void
    {
        DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $userId,
            'type' => 'App\\Notifications\\TrialStarted',
            'module' => 'core',
            'category' => 'info',
            'title' => 'Free Trial Started',
            'message' => "Your {$days}-day free trial for {$moduleId} has started!",
            'priority' => 'normal',
            'created_at' => now(),
        ]);
    }

    /**
     * Create upgrade notification
     */
    private function createUpgradeNotification(int $userId, string $moduleId, string $newTier): void
    {
        DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $userId,
            'type' => 'App\\Notifications\\SubscriptionUpgraded',
            'module' => 'core',
            'category' => 'success',
            'title' => 'Subscription Upgraded',
            'message' => "Your {$moduleId} subscription has been upgraded to {$newTier}!",
            'priority' => 'normal',
            'created_at' => now(),
        ]);
    }
}
