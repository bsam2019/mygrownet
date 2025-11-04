<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Custom LGR withdrawable percentage for this specific user
            // NULL means use the global setting
            $table->decimal('lgr_custom_withdrawable_percentage', 5, 2)->nullable()->after('loyalty_points_withdrawn_total');
            
            // Flag to completely block LGR withdrawals for this user
            $table->boolean('lgr_withdrawal_blocked')->default(false)->after('lgr_custom_withdrawable_percentage');
            
            // Reason for restriction (for admin reference)
            $table->text('lgr_restriction_reason')->nullable()->after('lgr_withdrawal_blocked');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'lgr_custom_withdrawable_percentage',
                'lgr_withdrawal_blocked',
                'lgr_restriction_reason'
            ]);
        });
    }
};
