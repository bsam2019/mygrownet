<?php

/**
 * Seed LGR Settings
 * 
 * Seeds the lgr_settings table with default values
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "🌱 Seeding LGR settings...\n\n";

$existingCount = DB::table('lgr_settings')->count();
echo "Current settings count: {$existingCount}\n";

if ($existingCount > 0) {
    echo "⚠️  Settings already exist. Skipping to avoid duplicates.\n";
    echo "If you want to reset, delete existing settings first.\n";
    exit(0);
}

DB::beginTransaction();
try {
    $settings = [
        // Withdrawal Settings
        [
            'key' => 'lgr_withdrawal_enabled',
            'value' => '1',
            'type' => 'boolean',
            'group' => 'withdrawal',
            'label' => 'Enable LGR Withdrawals',
            'description' => 'Allow members to withdraw directly from LGR balance',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'lgr_withdrawal_fee_percentage',
            'value' => '5',
            'type' => 'decimal',
            'group' => 'withdrawal',
            'label' => 'Withdrawal Fee (%)',
            'description' => 'Percentage fee charged on LGR withdrawals',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'lgr_withdrawal_min_amount',
            'value' => '50',
            'type' => 'decimal',
            'group' => 'withdrawal',
            'label' => 'Minimum Withdrawal Amount',
            'description' => 'Minimum amount that can be withdrawn from LGR',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'lgr_withdrawal_max_amount',
            'value' => '5000',
            'type' => 'decimal',
            'group' => 'withdrawal',
            'label' => 'Maximum Withdrawal Amount',
            'description' => 'Maximum amount that can be withdrawn from LGR per transaction',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        
        // Transfer Settings
        [
            'key' => 'lgr_transfer_enabled',
            'value' => '1',
            'type' => 'boolean',
            'group' => 'transfer',
            'label' => 'Enable LGR to Wallet Transfer',
            'description' => 'Allow members to transfer LGR balance to main wallet',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'lgr_transfer_fee_percentage',
            'value' => '0',
            'type' => 'decimal',
            'group' => 'transfer',
            'label' => 'Transfer Fee (%)',
            'description' => 'Percentage fee charged on LGR to wallet transfers',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'lgr_transfer_min_amount',
            'value' => '10',
            'type' => 'decimal',
            'group' => 'transfer',
            'label' => 'Minimum Transfer Amount',
            'description' => 'Minimum amount that can be transferred from LGR to wallet',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'lgr_transfer_max_amount',
            'value' => '10000',
            'type' => 'decimal',
            'group' => 'transfer',
            'label' => 'Maximum Transfer Amount',
            'description' => 'Maximum amount that can be transferred from LGR to wallet per transaction',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        
        // Manual Award Settings
        [
            'key' => 'lgr_manual_award_min',
            'value' => '10',
            'type' => 'decimal',
            'group' => 'awards',
            'label' => 'Minimum Manual Award',
            'description' => 'Minimum amount for manual LGR awards',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'key' => 'lgr_manual_award_max',
            'value' => '10000',
            'type' => 'decimal',
            'group' => 'awards',
            'label' => 'Maximum Manual Award',
            'description' => 'Maximum amount for manual LGR awards',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ];
    
    DB::table('lgr_settings')->insert($settings);
    
    DB::commit();
    
    $newCount = DB::table('lgr_settings')->count();
    echo "\n✅ Successfully seeded {$newCount} LGR settings!\n\n";
    
    echo "Settings by group:\n";
    $groups = DB::table('lgr_settings')
        ->select('group', DB::raw('count(*) as count'))
        ->groupBy('group')
        ->get();
    
    foreach ($groups as $group) {
        echo "  - {$group->group}: {$group->count} settings\n";
    }
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
