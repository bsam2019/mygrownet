#!/bin/bash

set -e

source .deploy-credentials

ssh -o StrictHostKeyChecking=no ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'

cd /var/www/mygrownet.com

echo "Investigating deposit sync discrepancy..."
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

// Get first unsynced deposit
\$deposit = DB::table('member_payments')
    ->where('payment_type', 'wallet_topup')
    ->where('status', 'verified')
    ->first();

echo \"\\nSample Deposit from member_payments:\\n\";
echo \"ID: {\$deposit->id}\\n\";
echo \"User ID: {\$deposit->user_id}\\n\";
echo \"Amount: {\$deposit->amount}\\n\";
echo \"Created: {\$deposit->created_at}\\n\";

// Check what transactions exist for this user
\$transactions = DB::table('transactions')
    ->where('user_id', \$deposit->user_id)
    ->get();

echo \"\\nTransactions for User {\$deposit->user_id}:\\n\";
foreach (\$transactions as \$tx) {
    echo \"  - Type: {\$tx->transaction_type}, Amount: {\$tx->amount}, Date: {\$tx->created_at}\\n\";
}

// Check if deposit transaction exists
\$depositTx = DB::table('transactions')
    ->where('user_id', \$deposit->user_id)
    ->where('transaction_type', 'deposit')
    ->where('amount', \$deposit->amount)
    ->whereDate('created_at', \$deposit->created_at)
    ->first();

if (\$depositTx) {
    echo \"\\n✅ Found matching transaction with type 'deposit'\\n\";
} else {
    echo \"\\n❌ No matching transaction found\\n\";
}

// Check all transaction types in the system
echo \"\\nAll transaction types in system:\\n\";
\$types = DB::table('transactions')
    ->select('transaction_type')
    ->distinct()
    ->get();
    
foreach (\$types as \$type) {
    \$count = DB::table('transactions')->where('transaction_type', \$type->transaction_type)->count();
    echo \"  - {\$type->transaction_type}: \$count\\n\";
}
"

ENDSSH
