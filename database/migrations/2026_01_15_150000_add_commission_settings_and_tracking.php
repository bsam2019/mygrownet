<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add commission tracking fields to referral_commissions table (if not exist)
        Schema::table('referral_commissions', function (Blueprint $table) {
            if (!Schema::hasColumn('referral_commissions', 'commission_base_percentage')) {
                $table->decimal('commission_base_percentage', 5, 2)->default(50.00);
            }
            if (!Schema::hasColumn('referral_commissions', 'non_kit_multiplier')) {
                $table->decimal('non_kit_multiplier', 5, 2)->default(1.00);
            }
            if (!Schema::hasColumn('referral_commissions', 'referrer_has_kit')) {
                $table->boolean('referrer_has_kit')->default(true);
            }
        });

        // Seed default commission settings
        $this->seedCommissionSettings();
    }

    public function down(): void
    {
        Schema::table('referral_commissions', function (Blueprint $table) {
            $columns = ['commission_base_percentage', 'non_kit_multiplier', 'referrer_has_kit'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('referral_commissions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Remove commission settings
        DB::table('system_settings')->where('key', 'like', 'commission_%')->delete();
    }

    private function seedCommissionSettings(): void
    {
        $settings = [
            [
                'key' => 'commission_base_percentage',
                'value' => json_encode(50),
                'description' => 'Percentage of purchase price used as commission base (default: 50%)',
            ],
            [
                'key' => 'commission_non_kit_multiplier',
                'value' => json_encode(50),
                'description' => 'Percentage of commission earned by members without starter kit (default: 50%)',
            ],
            [
                'key' => 'commission_level_rates',
                'value' => json_encode([
                    1 => 15.0,
                    2 => 10.0,
                    3 => 8.0,
                    4 => 6.0,
                    5 => 4.0,
                    6 => 3.0,
                    7 => 2.0,
                ]),
                'description' => 'Commission rates per level (1-7)',
            ],
            [
                'key' => 'commission_enabled',
                'value' => json_encode(true),
                'description' => 'Whether MLM commissions are enabled',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
};
