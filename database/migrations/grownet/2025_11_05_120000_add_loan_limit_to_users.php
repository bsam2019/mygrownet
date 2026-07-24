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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('loan_limit', 10, 2)->default(0)->after('loan_balance');
        });
        
        // Note: Loan limits are set to 0 by default
        // Admins must manually set loan limits for members who are eligible
        // This ensures proper risk management and approval process
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('loan_limit');
        });
    }
};
