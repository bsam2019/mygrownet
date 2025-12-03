<?php

namespace App\Listeners;

use App\Domain\Payment\Events\PaymentVerified;
use App\Services\MLMCommissionService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProcessMLMCommissions
{
    public function __construct(
        private readonly MLMCommissionService $mlmService
    ) {}

    /**
     * Handle the event - Process MLM commissions when payment is verified
     */
    public function handle(PaymentVerified $event): void
    {
        Log::info("ProcessMLMCommissions listener triggered", [
            'payment_id' => $event->paymentId,
            'user_id' => $event->userId,
            'amount' => $event->amount,
            'payment_type' => $event->paymentType
        ]);

        try {
            $user = User::find($event->userId);
            
            if (!$user) {
                Log::warning("User not found for commission processing", ['user_id' => $event->userId]);
                return;
            }

            Log::info("User found for commission processing", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'referrer_id' => $user->referrer_id,
                'status' => $user->status
            ]);

            // Only process commissions for registration and subscription payments
            if (!in_array($event->paymentType, ['wallet_topup', 'subscription', 'registration'])) {
                Log::info("Payment type not eligible for commissions", [
                    'payment_type' => $event->paymentType
                ]);
                return;
            }

            // Determine package type
            $packageType = null;
            
            if ($event->paymentType === 'registration' || ($event->paymentType === 'wallet_topup' && $event->amount >= 500)) {
                // Check if ANY commission already exists for this user (registration OR starter_kit)
                // This prevents duplicate commissions from different sources
                $hasAnyCommission = \App\Models\ReferralCommission::where('referred_id', $user->id)
                    ->whereIn('package_type', ['registration', 'starter_kit'])
                    ->exists();

                if ($hasAnyCommission) {
                    Log::info("Commission already processed for this user (registration or starter_kit), skipping", [
                        'user_id' => $user->id,
                        'payment_id' => $event->paymentId
                    ]);
                    return;
                }

                // Use 'starter_kit' as the package type for consistency
                // (registration and starter_kit are the same thing - K500 initial payment)
                $packageType = 'starter_kit';
            } elseif ($event->paymentType === 'subscription') {
                // Check if subscription commission already exists for this period
                $existingSubscriptionCommission = \App\Models\ReferralCommission::where('referred_id', $user->id)
                    ->where('package_type', 'subscription')
                    ->where('created_at', '>=', now()->subMinutes(5))
                    ->exists();

                if ($existingSubscriptionCommission) {
                    Log::info("Subscription commission already processed recently, skipping", [
                        'user_id' => $user->id,
                        'payment_id' => $event->paymentId
                    ]);
                    return;
                }

                $packageType = 'subscription';
            }

            if ($packageType) {
                // For starter kit purchases, always use K500 as commission base
                // even if they paid K1000 for premium (extra K500 is for content, not commissionable)
                $commissionAmount = $packageType === 'starter_kit' ? 500.00 : $event->amount;
                
                Log::info("Processing {$packageType} commissions", [
                    'user_id' => $user->id,
                    'payment_amount' => $event->amount,
                    'commission_amount' => $commissionAmount,
                    'payment_type' => $event->paymentType,
                    'note' => $packageType === 'starter_kit' ? 'Commission based on K500 only' : null,
                ]);

                $commissions = $this->mlmService->processMLMCommissions(
                    $user,
                    $commissionAmount,
                    $packageType
                );
                
                Log::info("{$packageType} commissions processed", [
                    'user_id' => $user->id,
                    'payment_amount' => $event->amount,
                    'commission_amount' => $commissionAmount,
                    'commissions_created' => count($commissions),
                    'total_commission_paid' => collect($commissions)->sum('amount'),
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to process MLM commissions", [
                'user_id' => $event->userId,
                'payment_id' => $event->paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw to ensure we see the error
            throw $e;
        }
    }
}
