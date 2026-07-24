<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add multi-currency support to referral_commissions table
        if (Schema::hasTable('referral_commissions')) {
            Schema::table('referral_commissions', function (Blueprint $table) {
                if (!Schema::hasColumn('referral_commissions', 'currency')) {
                    // Currency the commission is stored in (recipient's base currency)
                    $table->string('currency', 3)->default('ZMW');
                }
                
                if (!Schema::hasColumn('referral_commissions', 'original_amount')) {
                    // Original payment details (for cross-currency tracking)
                    $table->decimal('original_amount', 15, 2)->nullable();
                    $table->string('original_currency', 3)->nullable();
                    $table->decimal('exchange_rate', 10, 6)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('referral_commissions')) {
            Schema::table('referral_commissions', function (Blueprint $table) {
                $table->dropColumn(['currency', 'original_amount', 'original_currency', 'exchange_rate']);
            });
        }
    }
};
