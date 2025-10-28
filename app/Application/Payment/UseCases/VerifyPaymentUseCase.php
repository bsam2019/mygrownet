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
        
        // Generate receipt for the payment
        $this->generateReceipt($payment);

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
            
            // Handle Starter Kit payment (product type with K500 amount)
            if ($paymentType === 'product' && $payment->amount()->value() == 500 && !$user->has_starter_kit) {
                // Check if purchase already exists for this payment
                $existingPurchase = \App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel::where('user_id', $user->id)
                    ->where('payment_reference', $payment->paymentReference())
                    ->first();
                
                if (!$existingPurchase) {
                    $this->completeStarterKitPurchase($user, $payment);
                } else {
                    \Log::info('Starter Kit purchase already exists for this payment', [
                        'user_id' => $user->id,
                        'payment_id' => $payment->id(),
                    ]);
                }
            }
            
            // Create matrix position if user doesn't have one
            $this->ensureMatrixPosition($user);
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
    
    /**
     * Complete starter kit purchase after payment verification
     */
    private function completeStarterKitPurchase(\App\Models\User $user, $payment): void
    {
        try {
            $starterKitService = app(\App\Services\StarterKitService::class);
            
            // Create purchase record with 'completed' status since payment is already verified
            $purchase = \App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel::create([
                'user_id' => $user->id,
                'amount' => 500,
                'payment_method' => $payment->paymentMethod()->value,
                'payment_reference' => $payment->paymentReference(),
                'status' => 'completed',
                'invoice_number' => \App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel::generateInvoiceNumber(),
                'purchased_at' => now(),
            ]);
            
            // Grant access, add credit, create unlocks
            $starterKitService->completePurchase($purchase);
            
            \Log::info('Starter Kit purchase completed via payment verification', [
                'user_id' => $user->id,
                'payment_id' => $payment->id(),
                'invoice' => $purchase->invoice_number,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to complete starter kit purchase', [
                'user_id' => $user->id,
                'payment_id' => $payment->id(),
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Ensure user has a matrix position
     */
    private function ensureMatrixPosition(\App\Models\User $user): void
    {
        // Check if user already has a matrix position
        if ($user->getMatrixPosition()) {
            return;
        }
        
        // Find sponsor (referrer)
        $sponsor = $user->referrer;
        
        if (!$sponsor) {
            // Create root position for users without referrer
            \App\Models\MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => null,
                'level' => 0, // Root level
                'position' => 1,
                'is_active' => true,
                'placed_at' => now(),
            ]);
            return;
        }
        
        // Update sponsor's referral count
        $sponsor->incrementReferralCount();
        
        // Use matrix service to find next available position
        $matrixService = app(\App\Domain\Reward\Services\ReferralMatrixService::class);
        $availablePosition = $matrixService->findNextAvailablePosition($sponsor);
        
        if ($availablePosition) {
            \App\Models\MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => $availablePosition['sponsor_id'],
                'level' => $availablePosition['level'],
                'position' => $availablePosition['position'],
                'is_active' => true,
                'placed_at' => now(),
            ]);
        } else {
            // Fallback: create direct position under sponsor
            $directChildren = \App\Models\MatrixPosition::where('sponsor_id', $sponsor->id)
                ->where('level', 1)
                ->count();
                
            \App\Models\MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $directChildren + 1,
                'is_active' => true,
                'placed_at' => now(),
            ]);
        }
    }
    
    /**
     * Generate receipt for verified payment
     */
    private function generateReceipt($payment): void
    {
        try {
            // Get the Eloquent model for the payment
            $paymentModel = \App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel::find($payment->id());
            
            if (!$paymentModel) {
                \Log::warning('Payment model not found for receipt generation', ['payment_id' => $payment->id()]);
                return;
            }
            
            $receiptService = app(\App\Services\ReceiptService::class);
            $receipt = $receiptService->generatePaymentReceipt($paymentModel);
            
            // Email receipt to user
            $receiptService->emailReceipt(
                $paymentModel->user,
                $receipt->pdf_path,
                'MyGrowNet - Payment Receipt'
            );
            
            $receipt->update([
                'emailed' => true,
                'emailed_at' => now(),
            ]);
            
            \Log::info('Receipt generated and emailed', [
                'receipt_id' => $receipt->id,
                'user_id' => $paymentModel->user_id,
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the payment verification if receipt generation fails
            \Log::error('Failed to generate receipt: ' . $e->getMessage(), [
                'payment_id' => $payment->id(),
                'exception' => $e,
            ]);
        }
    }
}
