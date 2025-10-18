<?php

namespace App\Services;

use App\Models\User;
use App\Models\InvestmentTier;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

class SubscriptionBillingService
{
    protected MobileMoneyService $mobileMoneyService;
    
    public function __construct(MobileMoneyService $mobileMoneyService)
    {
        $this->mobileMoneyService = $mobileMoneyService;
    }

    /**
     * Process all due subscription payments
     */
    public function processAllDueSubscriptions(): array
    {
        $results = [
            'total_processed' => 0,
            'successful_payments' => 0,
            'failed_payments' => 0,
            'total_amount' => 0,
            'downgrades' => 0,
            'errors' => []
        ];

        try {
            // Get all users with due subscriptions
            $dueSubscriptions = $this->getDueSubscriptions();
            
            foreach ($dueSubscriptions as $subscription) {
                $result = $this->processSubscriptionPayment($subscription);
                
                $results['total_processed']++;
                $results['total_amount'] += $result['amount'];
                
                if ($result['success']) {
                    $results['successful_payments']++;
                } else {
                    $results['failed_payments']++;
                    if ($result['downgraded']) {
                        $results['downgrades']++;
                    }
                    if (!empty($result['error'])) {
                        $results['errors'][] = $result['error'];
                    }
                }
            }

            Log::info('Subscription billing batch processing completed', $results);

        } catch (Exception $e) {
            Log::error('Subscription billing batch processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $results['errors'][] = 'Batch processing failed: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Process subscription payment for a specific subscription
     */
    public function processSubscriptionPayment(Subscription $subscription): array
    {
        $user = $subscription->user;
        $tier = $subscription->tier;
        
        $result = [
            'success' => false,
            'amount' => $tier->monthly_fee,
            'downgraded' => false,
            'error' => null
        ];

        try {
            DB::beginTransaction();

            // Check if user has sufficient balance or valid payment method
            if ($user->balance >= $tier->monthly_fee) {
                // Deduct from user balance
                $success = $this->processBalancePayment($subscription);
                $result['success'] = $success;
                $result['payment_method'] = 'balance';
            } else {
                // Attempt mobile money payment
                $paymentResult = $this->processMobileMoneySubscriptionPayment($subscription);
                $result['success'] = $paymentResult['success'];
                $result['payment_method'] = 'mobile_money';
                
                if (!$paymentResult['success']) {
                    $result['error'] = $paymentResult['error'] ?? 'Mobile money payment failed';
                }
            }

            if ($result['success']) {
                // Extend subscription period
                $this->extendSubscription($subscription);
                
                // Record successful payment activity
                $user->recordActivity(
                    'subscription_payment_successful',
                    "Paid K{$tier->monthly_fee} for {$tier->name} subscription"
                );
            } else {
                // Handle failed payment
                $downgradeResult = $this->handleFailedSubscriptionPayment($subscription);
                $result['downgraded'] = $downgradeResult['downgraded'];
                
                if ($downgradeResult['downgraded']) {
                    $result['error'] = "Payment failed - downgraded to {$downgradeResult['new_tier']}";
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            
            $result['error'] = "Subscription payment exception: " . $e->getMessage();
            
            Log::error('Subscription payment processing failed', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Process payment using user's balance
     */
    protected function processBalancePayment(Subscription $subscription): bool
    {
        $user = $subscription->user;
        $tier = $subscription->tier;

        if ($user->balance < $tier->monthly_fee) {
            return false;
        }

        // Create payment transaction record
        $paymentTransaction = PaymentTransaction::create([
            'user_id' => $user->id,
            'type' => 'subscription_payment',
            'amount' => $tier->monthly_fee,
            'status' => 'completed',
            'payment_method' => 'wallet',
            'payment_details' => [
                'subscription_id' => $subscription->id,
                'tier_id' => $tier->id,
                'tier_name' => $tier->name,
                'billing_period' => 'monthly'
            ],
            'reference' => $this->generateSubscriptionReference($subscription->id),
            'completed_at' => now()
        ]);

        // Deduct amount from user balance
        $user->decrement('balance', $tier->monthly_fee);

        // Update subscription payment record
        $subscription->update([
            'last_payment_at' => now(),
            'last_payment_amount' => $tier->monthly_fee,
            'payment_transaction_id' => $paymentTransaction->id,
            'failed_payment_attempts' => 0 // Reset failed attempts
        ]);

        return true;
    }

    /**
     * Process mobile money subscription payment
     */
    protected function processMobileMoneySubscriptionPayment(Subscription $subscription): array
    {
        $user = $subscription->user;
        $tier = $subscription->tier;

        // Validate user payment details
        if (!$this->validateUserPaymentDetails($user)) {
            return [
                'success' => false,
                'error' => 'Invalid payment details'
            ];
        }

        try {
            // Create payment transaction record
            $paymentTransaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'type' => 'subscription_payment',
                'amount' => $tier->monthly_fee,
                'status' => 'pending',
                'payment_method' => 'mobile_money',
                'payment_details' => [
                    'phone_number' => $user->phone_number,
                    'subscription_id' => $subscription->id,
                    'tier_id' => $tier->id,
                    'tier_name' => $tier->name,
                    'billing_period' => 'monthly'
                ],
                'reference' => $this->generateSubscriptionReference($subscription->id)
            ]);

            // Process mobile money payment
            $paymentResult = $this->mobileMoneyService->sendPayment([
                'phone_number' => $user->phone_number,
                'amount' => $tier->monthly_fee,
                'reference' => $paymentTransaction->reference,
                'description' => "MyGrowNet {$tier->name} subscription - K{$tier->monthly_fee}",
                'recipient_name' => $user->name
            ]);

            if ($paymentResult['success']) {
                // Update payment transaction
                $paymentTransaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'external_reference' => $paymentResult['external_reference'] ?? null,
                    'payment_response' => $paymentResult
                ]);

                // Update subscription payment record
                $subscription->update([
                    'last_payment_at' => now(),
                    'last_payment_amount' => $tier->monthly_fee,
                    'payment_transaction_id' => $paymentTransaction->id,
                    'failed_payment_attempts' => 0 // Reset failed attempts
                ]);

                return [
                    'success' => true,
                    'transaction_id' => $paymentTransaction->id,
                    'external_reference' => $paymentResult['external_reference'] ?? null
                ];
            } else {
                // Mark payment as failed
                $paymentTransaction->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $paymentResult['error'] ?? 'Unknown error',
                    'payment_response' => $paymentResult
                ]);

                // Increment failed payment attempts
                $subscription->increment('failed_payment_attempts');

                return [
                    'success' => false,
                    'error' => $paymentResult['error'] ?? 'Mobile money payment failed',
                    'transaction_id' => $paymentTransaction->id
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Payment processing exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Handle failed subscription payment
     */
    protected function handleFailedSubscriptionPayment(Subscription $subscription): array
    {
        $user = $subscription->user;
        $currentTier = $subscription->tier;
        
        $result = [
            'downgraded' => false,
            'new_tier' => null
        ];

        // Check if user has exceeded maximum failed attempts
        $maxFailedAttempts = config('mygrownet.subscription.max_failed_attempts', 3);
        
        if ($subscription->failed_payment_attempts >= $maxFailedAttempts) {
            // Downgrade to lower tier or suspend
            $downgradeTier = $this->getDowngradeTier($currentTier);
            
            if ($downgradeTier) {
                // Downgrade to lower tier
                $this->downgradeSubscription($subscription, $downgradeTier);
                
                $result['downgraded'] = true;
                $result['new_tier'] = $downgradeTier->name;
                
                // Record downgrade activity
                $user->recordActivity(
                    'subscription_downgraded',
                    "Downgraded from {$currentTier->name} to {$downgradeTier->name} due to failed payments"
                );
                
                Log::info('User subscription downgraded', [
                    'user_id' => $user->id,
                    'from_tier' => $currentTier->name,
                    'to_tier' => $downgradeTier->name,
                    'failed_attempts' => $subscription->failed_payment_attempts
                ]);
            } else {
                // Suspend subscription (no lower tier available)
                $this->suspendSubscription($subscription);
                
                $result['downgraded'] = true;
                $result['new_tier'] = 'Suspended';
                
                // Record suspension activity
                $user->recordActivity(
                    'subscription_suspended',
                    "Subscription suspended due to failed payments"
                );
                
                Log::info('User subscription suspended', [
                    'user_id' => $user->id,
                    'tier' => $currentTier->name,
                    'failed_attempts' => $subscription->failed_payment_attempts
                ]);
            }
        }

        return $result;
    }

    /**
     * Get subscriptions that are due for payment
     */
    protected function getDueSubscriptions(): Collection
    {
        return Subscription::with(['user', 'tier'])
            ->where('status', 'active')
            ->where('next_billing_date', '<=', now())
            ->whereHas('user', function ($query) {
                $query->where('is_blocked', false);
            })
            ->get();
    }

    /**
     * Extend subscription period after successful payment
     */
    protected function extendSubscription(Subscription $subscription): void
    {
        $nextBillingDate = $subscription->next_billing_date->addMonth();
        
        $subscription->update([
            'next_billing_date' => $nextBillingDate,
            'status' => 'active',
            'suspended_at' => null
        ]);

        // Update user subscription fields
        $subscription->user->update([
            'subscription_status' => 'active',
            'subscription_end_date' => $nextBillingDate
        ]);
    }

    /**
     * Downgrade subscription to lower tier
     */
    protected function downgradeSubscription(Subscription $subscription, InvestmentTier $newTier): void
    {
        $user = $subscription->user;
        
        // Update subscription
        $subscription->update([
            'tier_id' => $newTier->id,
            'next_billing_date' => now()->addMonth(),
            'failed_payment_attempts' => 0,
            'downgraded_at' => now(),
            'downgrade_reason' => 'failed_payments'
        ]);

        // Update user tier
        $user->update([
            'current_investment_tier_id' => $newTier->id,
            'monthly_subscription_fee' => $newTier->monthly_fee
        ]);

        // Add to tier history
        $user->addTierHistory($newTier->id, 'Downgraded due to failed payments');
    }

    /**
     * Suspend subscription
     */
    protected function suspendSubscription(Subscription $subscription): void
    {
        $subscription->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspension_reason' => 'failed_payments'
        ]);

        // Update user subscription status
        $subscription->user->update([
            'subscription_status' => 'suspended'
        ]);
    }

    /**
     * Get appropriate downgrade tier
     */
    protected function getDowngradeTier(InvestmentTier $currentTier): ?InvestmentTier
    {
        return InvestmentTier::where('monthly_fee', '<', $currentTier->monthly_fee)
            ->orderBy('monthly_fee', 'desc')
            ->first();
    }

    /**
     * Validate user payment details
     */
    protected function validateUserPaymentDetails(User $user): bool
    {
        // Check if user has valid phone number for mobile money
        if (empty($user->phone_number)) {
            return false;
        }

        // Validate phone number format (Zambian format)
        if (!preg_match('/^(\+260|0)?[79]\d{8}$/', $user->phone_number)) {
            return false;
        }

        return true;
    }

    /**
     * Generate unique subscription payment reference
     */
    protected function generateSubscriptionReference(int $subscriptionId): string
    {
        return 'MGN-SUB-' . $subscriptionId . '-' . now()->format('YmdHis') . '-' . rand(1000, 9999);
    }

    /**
     * Create new subscription for user
     */
    public function createSubscription(User $user, InvestmentTier $tier): Subscription
    {
        return Subscription::create([
            'user_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => 'active',
            'started_at' => now(),
            'next_billing_date' => now()->addMonth(),
            'failed_payment_attempts' => 0
        ]);
    }

    /**
     * Upgrade user subscription to higher tier
     */
    public function upgradeSubscription(Subscription $subscription, InvestmentTier $newTier): bool
    {
        try {
            DB::beginTransaction();

            $user = $subscription->user;
            $oldTier = $subscription->tier;

            // Calculate prorated amount if upgrading mid-cycle
            $proratedAmount = $this->calculateProratedUpgradeAmount($subscription, $newTier);

            if ($proratedAmount > 0) {
                // Process upgrade payment
                $upgradeResult = $this->processUpgradePayment($user, $proratedAmount, $oldTier, $newTier);
                
                if (!$upgradeResult['success']) {
                    DB::rollBack();
                    return false;
                }
            }

            // Update subscription
            $subscription->update([
                'tier_id' => $newTier->id,
                'upgraded_at' => now(),
                'upgrade_reason' => 'user_requested'
            ]);

            // Update user tier
            $user->update([
                'current_investment_tier_id' => $newTier->id,
                'monthly_subscription_fee' => $newTier->monthly_fee
            ]);

            // Add to tier history
            $user->addTierHistory($newTier->id, "Upgraded from {$oldTier->name} to {$newTier->name}");

            // Record upgrade activity
            $user->recordActivity(
                'subscription_upgraded',
                "Upgraded subscription from {$oldTier->name} to {$newTier->name}"
            );

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Subscription upgrade failed', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'new_tier_id' => $newTier->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Calculate prorated amount for mid-cycle upgrade
     */
    protected function calculateProratedUpgradeAmount(Subscription $subscription, InvestmentTier $newTier): float
    {
        $currentTier = $subscription->tier;
        $priceDifference = $newTier->monthly_fee - $currentTier->monthly_fee;
        
        if ($priceDifference <= 0) {
            return 0; // No charge for downgrade or same tier
        }

        // Calculate remaining days in current billing cycle
        $daysInMonth = now()->daysInMonth;
        $daysPassed = now()->day;
        $remainingDays = $daysInMonth - $daysPassed;
        
        // Calculate prorated amount
        $dailyRate = $priceDifference / $daysInMonth;
        return $dailyRate * $remainingDays;
    }

    /**
     * Process upgrade payment
     */
    protected function processUpgradePayment(User $user, float $amount, InvestmentTier $oldTier, InvestmentTier $newTier): array
    {
        // Try balance payment first
        if ($user->balance >= $amount) {
            $user->decrement('balance', $amount);
            
            // Create payment transaction
            PaymentTransaction::create([
                'user_id' => $user->id,
                'type' => 'subscription_payment',
                'amount' => $amount,
                'status' => 'completed',
                'payment_method' => 'wallet',
                'payment_details' => [
                    'upgrade_from' => $oldTier->name,
                    'upgrade_to' => $newTier->name,
                    'prorated_amount' => $amount
                ],
                'reference' => 'MGN-UPG-' . $user->id . '-' . now()->format('YmdHis'),
                'completed_at' => now()
            ]);

            return ['success' => true, 'method' => 'balance'];
        }

        // Try mobile money payment
        if ($this->validateUserPaymentDetails($user)) {
            $paymentResult = $this->mobileMoneyService->sendPayment([
                'phone_number' => $user->phone_number,
                'amount' => $amount,
                'reference' => 'MGN-UPG-' . $user->id . '-' . now()->format('YmdHis'),
                'description' => "MyGrowNet upgrade to {$newTier->name} - K{$amount}",
                'recipient_name' => $user->name
            ]);

            if ($paymentResult['success']) {
                // Create payment transaction
                PaymentTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'subscription_payment',
                    'amount' => $amount,
                    'status' => 'completed',
                    'payment_method' => 'mobile_money',
                    'payment_details' => [
                        'phone_number' => $user->phone_number,
                        'upgrade_from' => $oldTier->name,
                        'upgrade_to' => $newTier->name,
                        'prorated_amount' => $amount
                    ],
                    'reference' => 'MGN-UPG-' . $user->id . '-' . now()->format('YmdHis'),
                    'external_reference' => $paymentResult['external_reference'] ?? null,
                    'completed_at' => now()
                ]);

                return ['success' => true, 'method' => 'mobile_money'];
            }
        }

        return ['success' => false, 'error' => 'Payment failed'];
    }

    /**
     * Get subscription statistics
     */
    public function getSubscriptionStatistics(string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $subscriptions = Subscription::with('tier')
            ->where('created_at', '>=', $startDate)
            ->get();

        $payments = PaymentTransaction::where('type', 'subscription_payment')
            ->where('created_at', '>=', $startDate)
            ->get();

        return [
            'total_subscriptions' => $subscriptions->count(),
            'active_subscriptions' => $subscriptions->where('status', 'active')->count(),
            'suspended_subscriptions' => $subscriptions->where('status', 'suspended')->count(),
            'total_revenue' => $payments->where('status', 'completed')->sum('amount'),
            'failed_payments' => $payments->where('status', 'failed')->count(),
            'success_rate' => $payments->count() > 0 
                ? ($payments->where('status', 'completed')->count() / $payments->count()) * 100 
                : 0,
            'by_tier' => $subscriptions->groupBy('tier.name')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'revenue' => $group->sum('tier.monthly_fee')
                ];
            }),
            'downgrades' => $subscriptions->whereNotNull('downgraded_at')->count(),
            'upgrades' => $subscriptions->whereNotNull('upgraded_at')->count()
        ];
    }

    /**
     * Retry failed subscription payments
     */
    public function retryFailedSubscriptionPayments(): array
    {
        $results = [
            'total_retried' => 0,
            'successful_retries' => 0,
            'failed_retries' => 0,
            'errors' => []
        ];

        // Get subscriptions with recent failed payments
        $failedSubscriptions = Subscription::with(['user', 'tier'])
            ->where('status', 'active')
            ->where('failed_payment_attempts', '>', 0)
            ->where('failed_payment_attempts', '<', config('mygrownet.subscription.max_failed_attempts', 3))
            ->where('updated_at', '>=', now()->subDays(7))
            ->get();

        foreach ($failedSubscriptions as $subscription) {
            $results['total_retried']++;
            
            $retryResult = $this->processSubscriptionPayment($subscription);
            
            if ($retryResult['success']) {
                $results['successful_retries']++;
            } else {
                $results['failed_retries']++;
                if (!empty($retryResult['error'])) {
                    $results['errors'][] = "Subscription {$subscription->id}: " . $retryResult['error'];
                }
            }
        }

        return $results;
    }
}