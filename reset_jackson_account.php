<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Resetting Jackson Phiri's Account\n";
echo "==================================\n\n";

$user = App\Models\User::find(31);

if (!$user) {
    echo "❌ User not found!\n";
    exit(1);
}

echo "User: {$user->name} ({$user->email})\n";
echo "Current Balance: K" . number_format($user->balance ?? 0, 2) . "\n\n";

// Find the purchase
$purchase = App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel::where('user_id', $user->id)->first();

if ($purchase) {
    echo "Found Purchase: {$purchase->invoice_number}\n";
    echo "Amount: K" . number_format($purchase->amount, 2) . "\n";
    echo "Status: {$purchase->status}\n";
    echo "Payment Method: {$purchase->payment_method}\n\n";
    
    // Refund the amount to wallet
    $refundAmount = $purchase->amount;
    echo "Refunding K{$refundAmount} to wallet...\n";
    
    $user->balance = ($user->balance ?? 0) + $refundAmount;
    $user->save();
    
    echo "✅ Refunded K{$refundAmount} to wallet\n";
    echo "New Balance: K" . number_format($user->balance, 2) . "\n\n";
    
    // Delete the purchase record
    echo "Deleting purchase record...\n";
    $purchase->delete();
    echo "✅ Purchase record deleted\n\n";
}

// Reset user starter kit fields
echo "Resetting user starter kit fields...\n";
$user->has_starter_kit = false;
$user->starter_kit_purchased_at = null;
$user->library_access_until = null;
$user->save();

echo "✅ User fields reset\n\n";

echo "Jackson's Account After Reset:\n";
echo "  Has Starter Kit: " . ($user->has_starter_kit ? 'YES' : 'NO') . "\n";
echo "  Wallet Balance: K" . number_format($user->balance, 2) . "\n";
echo "  Library Access: " . ($user->library_access_until ?? 'NULL') . "\n";
echo "\n✅ Account reset complete! Jackson can now purchase again.\n";
