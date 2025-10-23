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
        try {
            $user = User::find($event->userId);
            
            if (!$user) {
                Log::warning("User not found for commission processing", ['user_id' => $event->userId]);
                return;
            }

            // Only process commissions for registration and subscription payments
            if (!in_array($event->paymentType, ['wallet_topup', 'subscription', 'registration'])) {
                return;
            }

            // For registration payments (K500), process commissions
            if ($event->paymentType === 'wallet_topup' && $event->amount >= 500) {
                $this->mlmService->processMLMCommissions(
                    $user,
                    $event->amount,
                    'registration'
                );
                
                Log::info("Registration commissions processed", [
                    'user_id' => $user->id,
                    'amount' => $event->amount
                ]);
            }

            // For subscription payments, process commissions
            if ($event->paymentType === 'subscription') {
                $this->mlmService->processMLMCommissions(
                    $user,
                    $event->amount,
                    'subscription'
                );
                
                Log::info("Subscription commissions processed", [
                    'user_id' => $user->id,
                    'amount' => $event->amount
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to process MLM commissions", [
                'user_id' => $event->userId,
                'payment_id' => $event->paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
