<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Application\StarterKit\UseCases\PurchaseStarterKitUseCase;

echo "=== TESTING JASON'S STARTER KIT PURCHASE ===" . PHP_EOL . PHP_EOL;

$user = User::find(7);

echo "User: {$user->name}" . PHP_EOL;
echo "Has Starter Kit: " . ($user->has_starter_kit ? 'YES' : 'NO') . PHP_EOL;
echo PHP_EOL;

// Calculate wallet balance
$commissionEarnings = (float) ($user->referralCommissions()->where('status', 'paid')->sum('amount') ?? 0);
$profitEarnings = (float) ($user->profitShares()->sum('amount') ?? 0);
$walletTopups = (float) (DB::table('member_payments')
    ->where('user_id', $user->id)
    ->where('payment_type', 'wallet_topup')
    ->where('status', 'verified')
    ->sum('amount') ?? 0);
$totalEarnings = $commissionEarnings + $profitEarnings + $walletTopups;
$totalWithdrawals = (float) ($user->withdrawals()->where('status', 'approved')->sum('amount') ?? 0);
$workshopExpenses = (float) (DB::table('workshop_registrations')
    ->where('workshop_registrations.user_id', $user->id)
    ->whereIn('workshop_registrations.status', ['registered', 'attended', 'completed'])
    ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
    ->sum('workshops.price') ?? 0);
$walletBalance = $totalEarnings - $totalWithdrawals - $workshopExpenses;

echo "Wallet Balance: K" . number_format($walletBalance, 2) . PHP_EOL;
echo "Starter Kit Price: K500" . PHP_EOL;
echo "Can Afford: " . ($walletBalance >= 500 ? 'YES ✓' : 'NO ✗') . PHP_EOL;
echo PHP_EOL;

if ($user->has_starter_kit) {
    echo "❌ User already has starter kit!" . PHP_EOL;
    exit(0);
}

if ($walletBalance < 500) {
    echo "❌ Insufficient wallet balance!" . PHP_EOL;
    exit(1);
}

echo "Attempting to purchase starter kit with wallet..." . PHP_EOL;

try {
    $purchaseUseCase = app(PurchaseStarterKitUseCase::class);
    
    $result = $purchaseUseCase->execute($user, 'wallet');
    
    echo "✅ Purchase successful!" . PHP_EOL;
    echo "Message: {$result['message']}" . PHP_EOL;
    echo "Redirect: {$result['redirect']}" . PHP_EOL;
    echo PHP_EOL;
    
    // Verify user now has starter kit
    $user->refresh();
    echo "User now has starter kit: " . ($user->has_starter_kit ? 'YES ✓' : 'NO ✗') . PHP_EOL;
    
    // Check purchase record
    $purchase = DB::table('starter_kit_purchases')
        ->where('user_id', $user->id)
        ->latest()
        ->first();
    
    if ($purchase) {
        echo PHP_EOL . "Purchase Details:" . PHP_EOL;
        echo "  Invoice: {$purchase->invoice_number}" . PHP_EOL;
        echo "  Amount: K{$purchase->amount}" . PHP_EOL;
        echo "  Status: {$purchase->status}" . PHP_EOL;
        echo "  Payment Method: {$purchase->payment_method}" . PHP_EOL;
    }
    
} catch (\Exception $e) {
    echo "❌ Purchase failed!" . PHP_EOL;
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(1);
}
