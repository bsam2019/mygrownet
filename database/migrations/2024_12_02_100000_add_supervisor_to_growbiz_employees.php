<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds supervisor_id to support employee hierarchy:
     * - Business Owner (manager_id) owns all employees
     * - Supervisor (supervisor_id) manages specific employees
     * - Regular employees have no reports
     */
    public function up(): void
    {
        Schema::table('growbiz_employees', function (Blueprint $table) {
            $table->foreignId('supervisor_id')
                ->nullable()
                ->after('user_id')
                ->constrained('growbiz_employees')
                ->nullOnDelete();
            
            $table->index('supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('growbiz_employees', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn('supervisor_id');
        });
    }
};
