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

            // For registration payments (K500), process commissions
            if ($event->paymentType === 'wallet_topup' && $event->amount >= 500) {
                Log::info("Processing registration commissions", [
                    'user_id' => $user->id,
                    'amount' => $event->amount
                ]);

                $commissions = $this->mlmService->processMLMCommissions(
                    $user,
                    $event->amount,
                    'registration'
                );
                
                Log::info("Registration commissions processed", [
                    'user_id' => $user->id,
                    'amount' => $event->amount,
                    'commissions_created' => count($commissions)
                ]);
            }

            // For subscription payments, process commissions
            if ($event->paymentType === 'subscription') {
                Log::info("Processing subscription commissions", [
                    'user_id' => $user->id,
                    'amount' => $event->amount
                ]);

                $commissions = $this->mlmService->processMLMCommissions(
                    $user,
                    $event->amount,
                    'subscription'
                );
                
                Log::info("Subscription commissions processed", [
                    'user_id' => $user->id,
                    'amount' => $event->amount,
                    'commissions_created' => count($commissions)
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
