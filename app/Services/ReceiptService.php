<?php

namespace App\Services;

use App\Models\User;
use App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel;
use App\Models\Transaction;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class ReceiptService
{
    /**
     * Generate receipt for payment
     */
    public function generatePaymentReceipt(MemberPaymentModel $payment): Receipt
    {
        $receiptNumber = 'RCP-' . strtoupper(substr($payment->transaction_id ?? uniqid(), 0, 8));
        
        $data = [
            'receipt_number' => $receiptNumber,
            'date' => $payment->created_at->format('F d, Y'),
            'user' => $payment->user,
            'payment' => $payment,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'transaction_id' => $payment->transaction_id,
            'type' => 'Registration Payment',
            'description' => 'MyGrowNet Platform Registration Fee',
        ];
        
        $pdf = Pdf::loadView('receipts.payment', $data);
        
        $filename = "receipt_{$receiptNumber}.pdf";
        $path = storage_path("app/receipts/{$filename}");
        
        // Ensure directory exists
        if (!file_exists(storage_path('app/receipts'))) {
            mkdir(storage_path('app/receipts'), 0755, true);
        }
        
        $pdf->save($path);
        
        // Save receipt record
        $receipt = Receipt::create([
            'receipt_number' => $receiptNumber,
            'user_id' => $payment->user_id,
            'type' => 'payment',
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'transaction_reference' => $payment->transaction_id,
            'description' => 'MyGrowNet Platform Registration Fee',
            'pdf_path' => $path,
            'metadata' => [
                'payment_id' => $payment->id,
            ],
        ]);
        
        return $receipt;
    }
    
    /**
     * Generate receipt for starter kit purchase
     */
    public function generateStarterKitReceipt(User $user, float $amount, string $paymentMethod, ?string $transactionRef = null): Receipt
    {
        $receiptNumber = 'SKT-' . strtoupper(uniqid());
        
        $data = [
            'receipt_number' => $receiptNumber,
            'date' => now()->format('F d, Y'),
            'user' => $user,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'type' => 'Starter Kit Purchase',
            'description' => 'MyGrowNet Starter Kit - Digital Learning Package',
            'items' => [
                ['name' => 'Starter Kit Access', 'price' => $amount],
                ['name' => '30 Days Library Access', 'price' => 0],
                ['name' => 'K50 Shop Credit', 'price' => 0],
            ],
        ];
        
        $pdf = Pdf::loadView('receipts.payment', $data); // Reuse payment template
        
        $filename = "receipt_{$receiptNumber}.pdf";
        $path = storage_path("app/receipts/{$filename}");
        
        // Ensure directory exists
        if (!file_exists(storage_path('app/receipts'))) {
            mkdir(storage_path('app/receipts'), 0755, true);
        }
        
        $pdf->save($path);
        
        // Save receipt record
        $receipt = Receipt::create([
            'receipt_number' => $receiptNumber,
            'user_id' => $user->id,
            'type' => 'starter_kit',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_reference' => $transactionRef,
            'description' => 'MyGrowNet Starter Kit - Digital Learning Package',
            'pdf_path' => $path,
            'metadata' => [
                'items' => $data['items'],
            ],
        ]);
        
        return $receipt;
    }
    
    /**
     * Generate receipt for wallet transaction
     */
    public function generateWalletReceipt(Transaction $transaction): string
    {
        $receiptNumber = 'WLT-' . strtoupper(substr(md5($transaction->id), 0, 8));
        
        $data = [
            'receipt_number' => $receiptNumber,
            'date' => $transaction->created_at->format('F d, Y'),
            'user' => $transaction->user,
            'transaction' => $transaction,
            'amount' => abs($transaction->amount),
            'type' => ucfirst($transaction->type),
            'description' => $transaction->description ?? 'Wallet Transaction',
        ];
        
        $pdf = Pdf::loadView('receipts.wallet', $data);
        
        $filename = "receipt_{$receiptNumber}.pdf";
        $path = storage_path("app/receipts/{$filename}");
        
        // Ensure directory exists
        if (!file_exists(storage_path('app/receipts'))) {
            mkdir(storage_path('app/receipts'), 0755, true);
        }
        
        $pdf->save($path);
        
        return $path;
    }
    
    /**
     * Send receipt via email
     */
    public function emailReceipt(User $user, string $receiptPath, string $subject): void
    {
        Mail::send('emails.receipt', ['user' => $user], function ($message) use ($user, $receiptPath, $subject) {
            $message->to($user->email, $user->name)
                ->subject($subject)
                ->attach($receiptPath);
        });
    }
}
