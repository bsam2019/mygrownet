<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableExists = Schema::hasTable('lgr_settings');
        
        if (!$tableExists) {
            Schema::create('lgr_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, integer, decimal, boolean, json
            $table->string('group')->default('general'); // general, withdrawal, transfer, awards
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        }

        // Insert default settings (only if table is empty)
        if (DB::table('lgr_settings')->count() === 0) {
            DB::table('lgr_settings')->insert([
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
                'description' => 'Maximum amount that can be transferred per transaction',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Award Settings
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
                'value' => '2100',
                'type' => 'decimal',
                'group' => 'awards',
                'label' => 'Maximum Manual Award',
                'description' => 'Maximum amount for manual LGR awards',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // General Settings
            [
                'key' => 'lgr_system_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'general',
                'label' => 'Enable LGR System',
                'description' => 'Master switch for the entire LGR system',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'lgr_expiry_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'label' => 'Enable LGR Expiry',
                'description' => 'Whether LGR credits expire after a certain period',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'lgr_expiry_days',
                'value' => '365',
                'type' => 'integer',
                'group' => 'general',
                'label' => 'LGR Expiry Days',
                'description' => 'Number of days before LGR credits expire (if expiry enabled)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lgr_settings');
    }
};
