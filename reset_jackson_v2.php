<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Resetting Jackson Phiri's Account (v2)\n";
echo "=======================================\n\n";

$user = App\Models\User::find(31);

if (!$user) {
    echo "❌ User not found!\n";
    exit(1);
}

echo "User: {$user->name} ({$user->email})\n\n";

// Check if wallet table exists
$hasWalletTable = Schema::hasTable('wallets');
echo "Wallet table exists: " . ($hasWalletTable ? 'YES' : 'NO') . "\n";

if ($hasWalletTable) {
    $wallet = DB::table('wallets')->where('user_id', $user->id)->first();
    if ($wallet) {
        echo "Current Wallet Balance: K" . number_format($wallet->balance, 2) . "\n\n";
    } else {
        echo "No wallet found for user\n\n";
    }
}

// Find the purchase
$purchase = App\Infrastructure\Persistence\Eloquent\StarterKit\StarterKitPurchaseModel::where('user_id', $user->id)->first();

if ($purchase) {
    echo "Found Purchase: {$purchase->invoice_number}\n";
    echo "Amount: K" . number_format($purchase->amount, 2) . "\n";
    echo "Status: {$purchase->status}\n";
    echo "Payment Method: {$purchase->payment_method}\n\n";
    
    // Refund to wallet if wallet exists
    if ($hasWalletTable) {
        $refundAmount = $purchase->amount;
        echo "Refunding K{$refundAmount} to wallet...\n";
        
        DB::table('wallets')
            ->where('user_id', $user->id)
            ->increment('balance', $refundAmount);
        
        // Create transaction record
        if (Schema::hasTable('wallet_transactions')) {
            DB::table('wallet_transactions')->insert([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $refundAmount,
                'description' => 'Starter Kit purchase refund - Account reset',
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✅ Transaction record created\n";
        }
        
        $newBalance = DB::table('wallets')->where('user_id', $user->id)->value('balance');
        echo "✅ Refunded K{$refundAmount} to wallet\n";
        echo "New Balance: K" . number_format($newBalance, 2) . "\n\n";
    }
    
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

if ($hasWalletTable) {
    $wallet = DB::table('wallets')->where('user_id', $user->id)->first();
    if ($wallet) {
        echo "  Wallet Balance: K" . number_format($wallet->balance, 2) . "\n";
    }
}

echo "  Library Access: " . ($user->library_access_until ?? 'NULL') . "\n";
echo "\n✅ Account reset complete! Jackson can now purchase again.\n";
