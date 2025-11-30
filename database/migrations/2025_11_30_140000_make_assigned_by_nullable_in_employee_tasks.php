<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Makes assigned_by nullable to allow admins (who may not be employees) to create tasks.
     */
    public function up(): void
    {
        Schema::table('employee_tasks', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['assigned_by']);
            
            // Make the column nullable
            $table->foreignId('assigned_by')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable support
            $table->foreign('assigned_by')
                ->references('id')
                ->on('employees')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            
            $table->foreignId('assigned_by')->nullable(false)->change();
            
            $table->foreign('assigned_by')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');
        });
    }
};
