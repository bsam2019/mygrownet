<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lgr_settings')) {
            return;
        }

        if (Schema::hasColumn('lgr_settings', 'group')) {
            return;
        }

        Schema::table('lgr_settings', function (Blueprint $table) {
            $table->string('group')->default('general')->after('type');
            $table->string('label')->after('group');
        });

        // Update existing settings with groups and labels
        DB::table('lgr_settings')->where('key', 'lgr_enabled')->update([
            'group' => 'general',
            'label' => 'Enable LGR System'
        ]);
        
        DB::table('lgr_settings')->where('key', 'lgr_withdrawal_enabled')->update([
            'group' => 'withdrawal',
            'label' => 'Enable LGR Withdrawals'
        ]);
        
        DB::table('lgr_settings')->where('key', 'lgr_withdrawal_fee_percentage')->update([
            'group' => 'withdrawal',
            'label' => 'Withdrawal Fee (%)'
        ]);
        
        DB::table('lgr_settings')->where('key', 'lgr_withdrawal_min_amount')->update([
            'group' => 'withdrawal',
            'label' => 'Minimum Withdrawal Amount'
        ]);
        
        DB::table('lgr_settings')->where('key', 'lgr_withdrawal_max_amount')->update([
            'group' => 'withdrawal',
            'label' => 'Maximum Withdrawal Amount'
        ]);
        
        DB::table('lgr_settings')->where('key', 'lgr_transfer_enabled')->update([
            'group' => 'transfer',
            'label' => 'Enable LGR to Wallet Transfer'
        ]);
        
        DB::table('lgr_settings')->where('key', 'lgr_transfer_fee_percentage')->update([
            'group' => 'transfer',
            'label' => 'Transfer Fee (%)'
        ]);
        
        DB::table('lgr_settings')->where('key', 'lgr_transfer_min_amount')->update([
            'group' => 'transfer',
            'label' => 'Minimum Transfer Amount'
        ]);
        
        DB::table('lgr_settings')->where('key', 'lgr_transfer_max_amount')->update([
            'group' => 'transfer',
            'label' => 'Maximum Transfer Amount'
        ]);
        
        DB::table('lgr_settings')->where('key', 'lgr_manual_award_min')->update([
            'group' => 'awards',
            'label' => 'Minimum Manual Award'
        ]);
        
        DB::table('lgr_settings')->where('key', 'lgr_manual_award_max')->update([
            'group' => 'awards',
            'label' => 'Maximum Manual Award'
        ]);
    }

    public function down(): void
    {
        Schema::table('lgr_settings', function (Blueprint $table) {
            $table->dropColumn(['group', 'label']);
        });
    }
};
