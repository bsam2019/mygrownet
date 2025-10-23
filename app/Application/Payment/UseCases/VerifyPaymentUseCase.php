<?php

namespace App\Application\Payment\UseCases;

use App\Domain\Payment\Events\PaymentVerified;
use App\Domain\Payment\Repositories\MemberPaymentRepositoryInterface;
use Illuminate\Support\Facades\Event;

class VerifyPaymentUseCase
{
    public function __construct(
        private readonly MemberPaymentRepositoryInterface $paymentRepository
    ) {}

    public function execute(int $paymentId, int $adminId, ?string $adminNotes = null): void
    {
        $payment = $this->paymentRepository->findById($paymentId);
        
        if (!$payment) {
            throw new \DomainException('Payment not found');
        }

        $payment->verify($adminId, $adminNotes);
        
        $this->paymentRepository->save($payment);

        // Update user status when payment is verified
        $user = \App\Models\User::find($payment->userId());
        if ($user) {
            $paymentType = $payment->paymentType()->value;
            
            // For any payment (registration, subscription, etc), activate the user
            if ($user->status !== 'active') {
                $user->update(['status' => 'active']);
            }
            
            // Handle subscription-specific updates
            if ($paymentType === 'subscription') {
                $user->update([
                    'subscription_status' => 'active',
                    'subscription_end_date' => now()->addMonth(),
                ]);

                // Create or update subscription record
                $subscription = \App\Models\PackageSubscription::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->latest()
                    ->first();

                if ($subscription) {
                    $subscription->update([
                        'status' => 'active',
                        'activated_at' => now(),
                        'expires_at' => now()->addMonth(),
                    ]);
                }
            }
            
            // Handle registration payment
            if ($paymentType === 'wallet_topup' && $payment->amount()->value() >= 500) {
                // This is likely a registration payment (K500)
                // User is already activated above, just ensure they can access dashboard
                $user->update([
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            }
        }

        Event::dispatch(new PaymentVerified(
            paymentId: $payment->id(),
            userId: $payment->userId(),
            verifiedBy: $adminId,
            amount: $payment->amount()->value(),
            paymentType: $payment->paymentType()->value,
            occurredAt: $payment->verifiedAt()
        ));
    }
}
