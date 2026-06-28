<?php

namespace App\Application\Payment\UseCases;

use App\Domain\Payment\Repositories\MemberPaymentRepositoryInterface;
use App\Models\Receipt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ResetPaymentUseCase
{
    public function __construct(
        private readonly MemberPaymentRepositoryInterface $paymentRepository
    ) {}

    public function execute(int $paymentId, int $adminId, string $reason): void
    {
        $payment = $this->paymentRepository->findById($paymentId);
        
        if (!$payment) {
            throw new \DomainException('Payment not found');
        }

        if ($payment->status()->isPending()) {
            throw new \DomainException('Payment is already pending');
        }

        // Delete associated receipt before resetting payment
        $this->deleteAssociatedReceipt($paymentId);

        $payment->resetToPending($adminId, $reason);
        
        $this->paymentRepository->save($payment);
        
        Log::info('Payment reset to pending', [
            'payment_id' => $paymentId,
            'admin_id' => $adminId,
            'reason' => $reason,
        ]);
    }
    
    /**
     * Delete receipt associated with this payment
     */
    private function deleteAssociatedReceipt(int $paymentId): void
    {
        try {
            // Find receipt by payment metadata
            $receipt = Receipt::where('metadata->payment_id', $paymentId)->first();
            
            if ($receipt) {
                // Delete PDF file if it exists
                if ($receipt->pdf_path && file_exists($receipt->pdf_path)) {
                    unlink($receipt->pdf_path);
                    Log::info('Receipt PDF deleted', [
                        'receipt_id' => $receipt->id,
                        'pdf_path' => $receipt->pdf_path,
                    ]);
                }
                
                // Delete receipt record
                $receipt->delete();
                
                Log::info('Receipt deleted due to payment reset', [
                    'receipt_id' => $receipt->id,
                    'receipt_number' => $receipt->receipt_number,
                    'payment_id' => $paymentId,
                ]);
            }
        } catch (\Exception $e) {
            // Log but don't fail the reset if receipt deletion fails
            Log::error('Failed to delete receipt during payment reset', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
