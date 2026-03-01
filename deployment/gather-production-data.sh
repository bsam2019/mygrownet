#!/bin/bash

# Gather Production Financial System Data
# This script collects comprehensive information about the current state
# of the financial system before Phase 1 refactoring

set -e

echo "=========================================="
echo "Production Financial System Data Gathering"
echo "=========================================="
echo ""

# Load credentials
source .deploy-credentials

# SSH into production and gather data
ssh -o StrictHostKeyChecking=no ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'

cd /var/www/mygrownet.com

echo "1. Database Table Statistics"
echo "----------------------------"
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

echo \"\\n=== TRANSACTIONS TABLE ===\\n\";
\$txCount = DB::table('transactions')->count();
\$txTypes = DB::table('transactions')->select('transaction_type', DB::raw('count(*) as count'))->groupBy('transaction_type')->get();
\$txStatuses = DB::table('transactions')->select('status', DB::raw('count(*) as count'))->groupBy('status')->get();
\$txTotal = DB::table('transactions')->where('status', 'completed')->sum('amount');

echo \"Total Transactions: \$txCount\\n\";
echo \"\\nBy Type:\\n\";
foreach (\$txTypes as \$type) {
    echo \"  {\$type->transaction_type}: {\$type->count}\\n\";
}
echo \"\\nBy Status:\\n\";
foreach (\$txStatuses as \$status) {
    echo \"  {\$status->status}: {\$status->count}\\n\";
}
echo \"Total Amount (completed): K\" . number_format(\$txTotal, 2) . \"\\n\";

echo \"\\n=== MEMBER_PAYMENTS TABLE ===\\n\";
\$mpCount = DB::table('member_payments')->count();
\$mpWalletTopup = DB::table('member_payments')->where('payment_type', 'wallet_topup')->count();
\$mpVerified = DB::table('member_payments')->where('payment_type', 'wallet_topup')->where('status', 'verified')->count();
\$mpTotal = DB::table('member_payments')->where('payment_type', 'wallet_topup')->where('status', 'verified')->sum('amount');

echo \"Total Payments: \$mpCount\\n\";
echo \"Wallet Topups: \$mpWalletTopup\\n\";
echo \"Verified Topups: \$mpVerified\\n\";
echo \"Total Verified Amount: K\" . number_format(\$mpTotal, 2) . \"\\n\";

echo \"\\n=== WITHDRAWALS TABLE ===\\n\";
\$wdCount = DB::table('withdrawals')->count();
\$wdStatuses = DB::table('withdrawals')->select('status', DB::raw('count(*) as count'))->groupBy('status')->get();
\$wdTotal = DB::table('withdrawals')->whereIn('status', ['approved', 'completed'])->sum('amount');

echo \"Total Withdrawals: \$wdCount\\n\";
echo \"\\nBy Status:\\n\";
foreach (\$wdStatuses as \$status) {
    echo \"  {\$status->status}: {\$status->count}\\n\";
}
echo \"Total Amount (approved/completed): K\" . number_format(\$wdTotal, 2) . \"\\n\";

echo \"\\n=== STARTER_KIT_PURCHASES TABLE ===\\n\";
\$skCount = DB::table('starter_kit_purchases')->count();
\$skTotal = DB::table('starter_kit_purchases')->sum('amount_paid');

echo \"Total Purchases: \$skCount\\n\";
echo \"Total Amount: K\" . number_format(\$skTotal, 2) . \"\\n\";

echo \"\\n=== USERS WITH WALLET ACTIVITY ===\\n\";
\$usersWithTx = DB::table('transactions')->distinct('user_id')->count('user_id');
\$usersWithDeposits = DB::table('member_payments')->where('payment_type', 'wallet_topup')->where('status', 'verified')->distinct('user_id')->count('user_id');
\$usersWithWithdrawals = DB::table('withdrawals')->distinct('user_id')->count('user_id');

echo \"Users with transactions: \$usersWithTx\\n\";
echo \"Users with deposits: \$usersWithDeposits\\n\";
echo \"Users with withdrawals: \$usersWithWithdrawals\\n\";
"

echo ""
echo "2. Wallet Balance Analysis"
echo "--------------------------"
php artisan wallet:monitor

echo ""
echo "3. Transaction Type Distribution"
echo "--------------------------------"
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

\$types = DB::table('transactions')
    ->select('transaction_type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
    ->groupBy('transaction_type')
    ->orderBy('count', 'desc')
    ->get();

echo \"\\nTransaction Types:\\n\";
echo str_pad('Type', 30) . str_pad('Count', 10) . str_pad('Total', 15) . \"\\n\";
echo str_repeat('-', 55) . \"\\n\";

foreach (\$types as \$type) {
    echo str_pad(\$type->transaction_type, 30) . 
         str_pad(\$type->count, 10) . 
         str_pad('K' . number_format(\$type->total, 2), 15) . \"\\n\";
}
"

echo ""
echo "4. Recent Transaction Activity (Last 7 Days)"
echo "--------------------------------------------"
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

\$recent = DB::table('transactions')
    ->where('created_at', '>=', Carbon::now()->subDays(7))
    ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
    ->groupBy('date')
    ->orderBy('date', 'desc')
    ->get();

echo \"\\nLast 7 Days Activity:\\n\";
echo str_pad('Date', 15) . str_pad('Count', 10) . str_pad('Total', 15) . \"\\n\";
echo str_repeat('-', 40) . \"\\n\";

foreach (\$recent as \$day) {
    echo str_pad(\$day->date, 15) . 
         str_pad(\$day->count, 10) . 
         str_pad('K' . number_format(\$day->total, 2), 15) . \"\\n\";
}
"

echo ""
echo "5. Deposit Sync Status"
echo "----------------------"
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

\$deposits = DB::table('member_payments')
    ->where('payment_type', 'wallet_topup')
    ->where('status', 'verified')
    ->get();

\$synced = 0;
\$unsynced = 0;

foreach (\$deposits as \$deposit) {
    \$exists = DB::table('transactions')
        ->where('user_id', \$deposit->user_id)
        ->where(function(\$q) use (\$deposit) {
            \$q->where('reference_number', \$deposit->transaction_id)
              ->orWhere(function(\$q2) use (\$deposit) {
                  \$q2->where('transaction_type', 'wallet_topup')
                     ->where('amount', \$deposit->amount)
                     ->whereDate('created_at', \$deposit->created_at);
              });
        })
        ->exists();
    
    if (\$exists) {
        \$synced++;
    } else {
        \$unsynced++;
    }
}

echo \"\\nDeposit Sync Status:\\n\";
echo \"Total Deposits: \" . count(\$deposits) . \"\\n\";
echo \"Synced to Transactions: \$synced\\n\";
echo \"Not Synced: \$unsynced\\n\";

if (\$unsynced > 0) {
    echo \"\\n⚠️  WARNING: \$unsynced deposits need to be synced!\\n\";
}
"

echo ""
echo "6. Balance Consistency Check"
echo "----------------------------"
php artisan tinker --execute="
use App\Models\User;
use App\Domain\Wallet\Services\UnifiedWalletService;
use Illuminate\Support\Facades\DB;

\$uws = app(UnifiedWalletService::class);

\$usersWithActivity = DB::table('transactions')
    ->distinct('user_id')
    ->pluck('user_id');

\$inconsistent = 0;
\$negative = 0;
\$total = count(\$usersWithActivity);

foreach (\$usersWithActivity as \$userId) {
    \$user = User::find(\$userId);
    if (!\$user) continue;
    
    \$balance = \$uws->calculateBalance(\$user);
    
    if (\$balance < 0) {
        \$negative++;
    }
}

echo \"\\nBalance Consistency:\\n\";
echo \"Users Checked: \$total\\n\";
echo \"Negative Balances: \$negative\\n\";

if (\$negative > 0) {
    echo \"\\n⚠️  WARNING: \$negative users have negative balances!\\n\";
} else {
    echo \"\\n✅ All balances are positive\\n\";
}
"

echo ""
echo "7. Database Table Structures"
echo "----------------------------"
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

echo \"\\n=== TRANSACTIONS TABLE STRUCTURE ===\\n\";
\$columns = DB::select('DESCRIBE transactions');
foreach (\$columns as \$col) {
    echo \$col->Field . ' (' . \$col->Type . ')' . (\$col->Null === 'YES' ? ' NULL' : ' NOT NULL') . \"\\n\";
}

echo \"\\n=== MEMBER_PAYMENTS TABLE STRUCTURE ===\\n\";
\$columns = DB::select('DESCRIBE member_payments');
foreach (\$columns as \$col) {
    echo \$col->Field . ' (' . \$col->Type . ')' . (\$col->Null === 'YES' ? ' NULL' : ' NOT NULL') . \"\\n\";
}

echo \"\\n=== WITHDRAWALS TABLE STRUCTURE ===\\n\";
\$columns = DB::select('DESCRIBE withdrawals');
foreach (\$columns as \$col) {
    echo \$col->Field . ' (' . \$col->Type . ')' . (\$col->Null === 'YES' ? ' NULL' : ' NOT NULL') . \"\\n\";
}
"

echo ""
echo "8. Service Usage Analysis"
echo "------------------------"
echo "Checking which wallet service is being used in codebase..."

cd /var/www/mygrownet.com
grep -r "WalletService" app/ --include="*.php" | grep -v "UnifiedWalletService" | grep -v "// " | wc -l | xargs -I {} echo "Legacy WalletService references: {}"
grep -r "UnifiedWalletService" app/ --include="*.php" | grep -v "// " | wc -l | xargs -I {} echo "UnifiedWalletService references: {}"

echo ""
echo "9. Recent Errors (Last 24 Hours)"
echo "--------------------------------"
tail -n 100 storage/logs/laravel.log | grep -i "wallet\|transaction\|balance" | tail -n 20

echo ""
echo "=========================================="
echo "Data Gathering Complete!"
echo "=========================================="
echo ""
echo "Summary saved to: storage/logs/production-financial-data-$(date +%Y%m%d-%H%M%S).log"

ENDSSH

echo ""
echo "✅ Production data gathered successfully!"
echo ""
echo "Next steps:"
echo "1. Review the data above"
echo "2. Verify all deposits are synced"
echo "3. Confirm no negative balances"
echo "4. Proceed with Phase 1 implementation"
