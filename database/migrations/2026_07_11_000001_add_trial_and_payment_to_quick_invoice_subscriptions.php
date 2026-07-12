<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quick_invoice_user_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('quick_invoice_user_subscriptions', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('expires_at');
            }
            if (!Schema::hasColumn('quick_invoice_user_subscriptions', 'billing_cycle')) {
                $table->string('billing_cycle', 20)->default('monthly')->after('trial_ends_at');
            }
            if (!Schema::hasColumn('quick_invoice_user_subscriptions', 'last_payment_at')) {
                $table->timestamp('last_payment_at')->nullable()->after('billing_cycle');
            }
            if (!Schema::hasColumn('quick_invoice_user_subscriptions', 'last_payment_amount')) {
                $table->decimal('last_payment_amount', 10, 2)->nullable()->after('last_payment_at');
            }
            if (!Schema::hasColumn('quick_invoice_user_subscriptions', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('last_payment_amount');
            }
            if (!Schema::hasColumn('quick_invoice_user_subscriptions', 'payment_reference')) {
                $table->string('payment_reference', 100)->nullable()->after('payment_method');
            }
        });

        $settingTable = 'quick_invoice_admin_settings';
        if (Schema::hasTable($settingTable)) {
            $exists = DB::table($settingTable)->where('setting_key', 'trial_settings')->exists();
            if (!$exists) {
                DB::table($settingTable)->insert([
                    'setting_key' => 'trial_settings',
                    'setting_value' => json_encode([
                        'trial_days' => 30,
                        'tier_on_trial' => 'Basic',
                        'require_payment_after_trial' => true,
                    ]),
                    'updated_by' => null,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('quick_invoice_user_subscriptions', function (Blueprint $table) {
            $columns = ['trial_ends_at', 'billing_cycle', 'last_payment_at', 'last_payment_amount', 'payment_method', 'payment_reference'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('quick_invoice_user_subscriptions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
