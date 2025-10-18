<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_commissions', function (Blueprint $table) {
            // Add status as an alias for payment_status
            $table->enum('status', ['pending', 'paid', 'cancelled'])->nullable()->after('payment_status');
            // Add calculation_date as an alias for earned_date
            $table->date('calculation_date')->nullable()->after('earned_date');
        });
        
        // Copy existing data
        DB::statement('UPDATE employee_commissions SET status = payment_status WHERE status IS NULL');
        DB::statement('UPDATE employee_commissions SET calculation_date = earned_date WHERE calculation_date IS NULL');
        
        // Make columns not nullable
        Schema::table('employee_commissions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid', 'cancelled'])->nullable(false)->change();
            $table->date('calculation_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_commissions', function (Blueprint $table) {
            $table->dropColumn(['status', 'calculation_date']);
        });
    }
};
