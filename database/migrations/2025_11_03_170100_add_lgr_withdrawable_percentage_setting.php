<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add withdrawable percentage setting
        DB::table('lgr_settings')->insert([
            'key' => 'lgr_withdrawable_percentage',
            'value' => '100',
            'type' => 'decimal',
            'group' => 'general',
            'label' => 'LGR Withdrawable Percentage',
            'description' => 'Percentage of total awarded LGR that can be withdrawn/transferred (e.g., 40 means 40% of awarded amount)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('lgr_settings')->where('key', 'lgr_withdrawable_percentage')->delete();
    }
};
