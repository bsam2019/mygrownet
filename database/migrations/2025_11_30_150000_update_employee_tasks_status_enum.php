<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Updates the status ENUM to include 'pending' and 'on_hold' values
     * that are expected by the TaskManagementController.
     */
    public function up(): void
    {
        // For MySQL, we need to modify the ENUM directly
        DB::statement("ALTER TABLE employee_tasks MODIFY COLUMN status ENUM('todo', 'pending', 'in_progress', 'review', 'completed', 'cancelled', 'on_hold') DEFAULT 'todo'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any 'pending' or 'on_hold' to 'todo' before reverting
        DB::table('employee_tasks')->where('status', 'pending')->update(['status' => 'todo']);
        DB::table('employee_tasks')->where('status', 'on_hold')->update(['status' => 'todo']);
        
        DB::statement("ALTER TABLE employee_tasks MODIFY COLUMN status ENUM('todo', 'in_progress', 'review', 'completed', 'cancelled') DEFAULT 'todo'");
    }
};
