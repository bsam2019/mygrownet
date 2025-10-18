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
            // Add commission_amount as an alias/copy of amount column
            $table->decimal('commission_amount', 10, 2)->nullable()->after('amount');
        });
        
        // Copy existing data from amount to commission_amount
        DB::statement('UPDATE employee_commissions SET commission_amount = amount WHERE commission_amount IS NULL');
        
        // Make commission_amount not nullable
        Schema::table('employee_commissions', function (Blueprint $table) {
            $table->decimal('commission_amount', 10, 2)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_commissions', function (Blueprint $table) {
            $table->dropColumn('commission_amount');
        });
    }
};
