#!/bin/bash

# Fix Deposit Sync Issue
# The member_payments table doesn't have transaction_id column
# We need to check sync status differently

set -e

echo "=========================================="
echo "Fixing Deposit Sync Check"
echo "=========================================="
echo ""

# Load credentials
source .deploy-credentials

# SSH into production and fix sync
ssh -o StrictHostKeyChecking=no ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'

cd /var/www/mygrownet.com

echo "Checking deposit sync status (fixed query)..."
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;

\$deposits = DB::table('member_payments')
    ->where('payment_type', 'wallet_topup')
    ->where('status', 'verified')
    ->get();

\$synced = 0;
\$unsynced = 0;
\$unsyncedList = [];

foreach (\$deposits as \$deposit) {
    // Check if transaction exists for this deposit
    \$exists = DB::table('transactions')
        ->where('user_id', \$deposit->user_id)
        ->where(function(\$q) use (\$deposit) {
            \$q->where('transaction_type', 'wallet_topup')
              ->where('amount', \$deposit->amount)
              ->whereDate('created_at', \$deposit->created_at);
        })
        ->exists();
    
    if (\$exists) {
        \$synced++;
    } else {
        \$unsynced++;
        \$unsyncedList[] = [
            'id' => \$deposit->id,
            'user_id' => \$deposit->user_id,
            'amount' => \$deposit->amount,
            'date' => \$deposit->created_at
        ];
    }
}

echo \"\\nDeposit Sync Status:\\n\";
echo \"Total Deposits: \" . count(\$deposits) . \"\\n\";
echo \"Synced to Transactions: \$synced\\n\";
echo \"Not Synced: \$unsynced\\n\\n\";

if (\$unsynced > 0) {
    echo \"Unsynced Deposits:\\n\";
    foreach (\$unsyncedList as \$item) {
        echo \"  - ID: {\$item['id']}, User: {\$item['user_id']}, Amount: K{\$item['amount']}, Date: {\$item['date']}\\n\";
    }
    echo \"\\n⚠️  Running sync now...\\n\\n\";
}
"

if [ $? -eq 0 ]; then
    echo "Running deposit sync command..."
    php artisan finance:sync-deposits
    
    echo ""
    echo "Verifying sync..."
    php artisan tinker --execute="
    use Illuminate\Support\Facades\DB;
    
    \$deposits = DB::table('member_payments')
        ->where('payment_type', 'wallet_topup')
        ->where('status', 'verified')
        ->get();
    
    \$synced = 0;
    foreach (\$deposits as \$deposit) {
        \$exists = DB::table('transactions')
            ->where('user_id', \$deposit->user_id)
            ->where(function(\$q) use (\$deposit) {
                \$q->where('transaction_type', 'wallet_topup')
                  ->where('amount', \$deposit->amount)
                  ->whereDate('created_at', \$deposit->created_at);
            })
            ->exists();
        
        if (\$exists) {
            \$synced++;
        }
    }
    
    echo \"\\n✅ Sync Complete!\\n\";
    echo \"Total Deposits: \" . count(\$deposits) . \"\\n\";
    echo \"Synced: \$synced\\n\";
    "
fi

ENDSSH

echo ""
echo "✅ Deposit sync fixed!"
