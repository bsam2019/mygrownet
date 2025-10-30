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
        
        // Itemized breakdown showing actual value and discount
        $items = [
            ['name' => 'Business Training Modules (3 Courses)', 'price' => 250.00],
            ['name' => 'Educational eBooks Collection (3 eBooks)', 'price' => 150.00],
            ['name' => 'Video Tutorial Series (3 Videos)', 'price' => 200.00],
            ['name' => 'Marketing Tools & Templates', 'price' => 100.00],
            ['name' => '30 Days Premium Library Access', 'price' => 150.00],
            ['name' => 'K100 Shop Credit (90-day validity)', 'price' => 100.00],
            ['name' => '25 Lifetime Points Bonus', 'price' => 50.00],
        ];
        
        $subtotal = array_sum(array_column($items, 'price'));
        $discount = $subtotal - $amount;
        
        $data = [
            'receipt_number' => $receiptNumber,
            'date' => now()->format('F d, Y'),
            'user' => $user,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'type' => 'Starter Kit Purchase',
            'description' => 'MyGrowNet Starter Kit - Digital Learning Package',
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'show_discount' => true,
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
            'user_id' => $user->id,
            'type' => 'starter_kit',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_reference' => $transactionRef,
            'description' => 'MyGrowNet Starter Kit - Digital Learning Package',
            'pdf_path' => $path,
            'metadata' => [
                'items' => $items,
                'subtotal' => $subtotal,
                'discount' => $discount,
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
    
    /**
     * Generate receipt for workshop registration
     */
    public function generateWorkshopReceipt(User $user, $workshop, string $paymentMethod, float $amount, ?string $transactionRef = null): Receipt
    {
        $receiptNumber = 'WKS-' . strtoupper(uniqid());
        
        $data = [
            'receipt_number' => $receiptNumber,
            'date' => now()->format('F d, Y'),
            'user' => $user,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionRef,
            'type' => 'Workshop Registration',
            'description' => 'Workshop: ' . $workshop->title,
            'items' => [
                ['name' => $workshop->title, 'price' => $amount],
                ['name' => 'Workshop Materials', 'price' => 0],
                ['name' => 'Certificate of Completion', 'price' => 0],
            ],
        ];
        
        $pdf = Pdf::loadView('receipts.payment', $data);
        
        $filename = "receipt_{$receiptNumber}.pdf";
        $path = storage_path("app/receipts/{$filename}");
        
        if (!file_exists(storage_path('app/receipts'))) {
            mkdir(storage_path('app/receipts'), 0755, true);
        }
        
        $pdf->save($path);
        
        $receipt = Receipt::create([
            'receipt_number' => $receiptNumber,
            'user_id' => $user->id,
            'type' => 'workshop',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_reference' => $transactionRef,
            'description' => 'Workshop: ' . $workshop->title,
            'pdf_path' => $path,
            'metadata' => [
                'workshop_id' => $workshop->id,
                'workshop_title' => $workshop->title,
                'items' => $data['items'],
            ],
        ]);
        
        return $receipt;
    }
    
    /**
     * Generate receipt for subscription payment
     */
    public function generateSubscriptionReceipt(User $user, $subscription, string $paymentMethod, float $amount, ?string $transactionRef = null): Receipt
    {
        $receiptNumber = 'SUB-' . strtoupper(uniqid());
        
        $data = [
            'receipt_number' => $receiptNumber,
            'date' => now()->format('F d, Y'),
            'user' => $user,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionRef,
            'type' => 'Subscription Payment',
            'description' => 'MyGrowNet ' . $subscription->package_name . ' Subscription',
            'items' => [
                ['name' => $subscription->package_name . ' Subscription', 'price' => $amount],
                ['name' => 'Duration: ' . $subscription->duration_months . ' month(s)', 'price' => 0],
            ],
        ];
        
        $pdf = Pdf::loadView('receipts.payment', $data);
        
        $filename = "receipt_{$receiptNumber}.pdf";
        $path = storage_path("app/receipts/{$filename}");
        
        if (!file_exists(storage_path('app/receipts'))) {
            mkdir(storage_path('app/receipts'), 0755, true);
        }
        
        $pdf->save($path);
        
        $receipt = Receipt::create([
            'receipt_number' => $receiptNumber,
            'user_id' => $user->id,
            'type' => 'subscription',
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_reference' => $transactionRef,
            'description' => 'MyGrowNet ' . $subscription->package_name . ' Subscription',
            'pdf_path' => $path,
            'metadata' => [
                'subscription_id' => $subscription->id,
                'package_name' => $subscription->package_name,
                'duration_months' => $subscription->duration_months,
                'items' => $data['items'],
            ],
        ]);
        
        return $receipt;
    }
    
    /**
     * Generate receipt for shop product purchase
     */
    public function generateShopReceipt(User $user, array $items, string $paymentMethod, float $totalAmount, ?string $transactionRef = null): Receipt
    {
        $receiptNumber = 'SHP-' . strtoupper(uniqid());
        
        $data = [
            'receipt_number' => $receiptNumber,
            'date' => now()->format('F d, Y'),
            'user' => $user,
            'amount' => $totalAmount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionRef,
            'type' => 'Shop Purchase',
            'description' => 'MyGrowNet Shop - ' . count($items) . ' item(s)',
            'items' => $items,
        ];
        
        $pdf = Pdf::loadView('receipts.payment', $data);
        
        $filename = "receipt_{$receiptNumber}.pdf";
        $path = storage_path("app/receipts/{$filename}");
        
        if (!file_exists(storage_path('app/receipts'))) {
            mkdir(storage_path('app/receipts'), 0755, true);
        }
        
        $pdf->save($path);
        
        $receipt = Receipt::create([
            'receipt_number' => $receiptNumber,
            'user_id' => $user->id,
            'type' => 'shop_purchase',
            'amount' => $totalAmount,
            'payment_method' => $paymentMethod,
            'transaction_reference' => $transactionRef,
            'description' => 'MyGrowNet Shop - ' . count($items) . ' item(s)',
            'pdf_path' => $path,
            'metadata' => [
                'items' => $items,
                'item_count' => count($items),
            ],
        ]);
        
        return $receipt;
    }
    
    /**
     * Generic method to create receipt for any transaction type
     */
    public function createReceipt(array $data): Receipt
    {
        $type = $data['type'] ?? 'general';
        $prefixes = [
            'venture_investment' => 'VNT',
            'wallet_topup' => 'WLT',
            'withdrawal' => 'WDR',
            'commission' => 'COM',
            'general' => 'RCP',
        ];
        
        $prefix = $prefixes[$type] ?? 'RCP';
        $receiptNumber = $prefix . '-' . strtoupper(uniqid());
        
        $user = User::find($data['user_id']);
        
        $pdfData = [
            'receipt_number' => $receiptNumber,
            'date' => now()->format('F d, Y'),
            'user' => $user,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? 'N/A',
            'transaction_id' => $data['payment_reference'] ?? null,
            'type' => $this->getTypeLabel($type),
            'description' => $data['description'],
            'items' => $data['items'] ?? null,
        ];
        
        $pdf = Pdf::loadView('receipts.payment', $pdfData);
        
        $filename = "receipt_{$receiptNumber}.pdf";
        $path = storage_path("app/receipts/{$filename}");
        
        if (!file_exists(storage_path('app/receipts'))) {
            mkdir(storage_path('app/receipts'), 0755, true);
        }
        
        $pdf->save($path);
        
        $receipt = Receipt::create([
            'receipt_number' => $receiptNumber,
            'user_id' => $data['user_id'],
            'type' => $type,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? null,
            'transaction_reference' => $data['payment_reference'] ?? null,
            'description' => $data['description'],
            'pdf_path' => $path,
            'metadata' => [
                'reference_id' => $data['reference_id'] ?? null,
                'items' => $data['items'] ?? null,
            ],
        ]);
        
        return $receipt;
    }
    
    /**
     * Get human-readable label for receipt type
     */
    private function getTypeLabel(string $type): string
    {
        $labels = [
            'venture_investment' => 'Venture Investment',
            'wallet_topup' => 'Wallet Top-up',
            'withdrawal' => 'Withdrawal',
            'commission' => 'Commission Payment',
            'starter_kit' => 'Starter Kit Purchase',
            'workshop' => 'Workshop Registration',
            'subscription' => 'Subscription Payment',
            'shop_purchase' => 'Shop Purchase',
            'payment' => 'Registration Payment',
            'general' => 'Transaction',
        ];
        
        return $labels[$type] ?? 'Transaction';
    }
}
