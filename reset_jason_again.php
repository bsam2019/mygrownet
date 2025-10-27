<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

echo "=== CHECKING JASON'S CURRENT STATUS ===" . PHP_EOL . PHP_EOL;

$user = User::find(7);

echo "User Status:" . PHP_EOL;
echo "  Has Starter Kit: " . ($user->has_starter_kit ? 'YES' : 'NO') . PHP_EOL;
echo "  Shop Credit: K" . ($user->starter_kit_shop_credit ?? 0) . PHP_EOL;
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

// Check for starter kit transactions
$starterKitTransactions = DB::table('transactions')
    ->where('user_id', 7)
    ->where('description', 'LIKE', '%Starter Kit%')
    ->get();

$walletBalance = $totalEarnings - $totalWithdrawals - $workshopExpenses;

echo "Wallet Balance: K" . number_format($walletBalance, 2) . PHP_EOL;
echo PHP_EOL;

if ($starterKitTransactions->count() > 0) {
    echo "Starter Kit Transactions Found:" . PHP_EOL;
    foreach ($starterKitTransactions as $t) {
        echo "  - {$t->transaction_type} | K{$t->amount} | {$t->description}" . PHP_EOL;
    }
} else {
    echo "No Starter Kit transactions found (this is the problem!)" . PHP_EOL;
}

echo PHP_EOL;

// Check purchases
$purchases = DB::table('starter_kit_purchases')
    ->where('user_id', 7)
    ->get();

if ($purchases->count() > 0) {
    echo "Starter Kit Purchases:" . PHP_EOL;
    foreach ($purchases as $p) {
        echo "  - Invoice: {$p->invoice_number} | Amount: K{$p->amount} | Status: {$p->status} | Method: {$p->payment_method}" . PHP_EOL;
    }
    echo PHP_EOL;
}

if ($user->has_starter_kit && $walletBalance >= 500) {
    echo "⚠️  ISSUE DETECTED: Jason has starter kit but wallet wasn't debited!" . PHP_EOL;
    echo PHP_EOL;
    echo "Resetting Jason's purchase..." . PHP_EOL;
    
    // Delete purchases
    DB::table('starter_kit_purchases')->where('user_id', 7)->delete();
    echo "  ✓ Purchases deleted" . PHP_EOL;
    
    // Reset user status
    $user->update([
        'has_starter_kit' => false,
        'starter_kit_purchased_at' => null,
        'starter_kit_shop_credit' => 0,
        'starter_kit_credit_expiry' => null,
        'library_access_until' => null,
    ]);
    echo "  ✓ User status reset" . PHP_EOL;
    
    // Delete unlocks
    $unlocks = DB::table('starter_kit_unlocks')->where('user_id', 7)->count();
    if ($unlocks > 0) {
        DB::table('starter_kit_unlocks')->where('user_id', 7)->delete();
        echo "  ✓ {$unlocks} unlocks deleted" . PHP_EOL;
    }
    
    echo PHP_EOL;
    echo "✅ Jason is reset and ready for proper purchase!" . PHP_EOL;
} else {
    echo "✅ Jason's status looks correct." . PHP_EOL;
}
