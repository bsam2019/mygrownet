<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            // First, modify the status enum to include 'rejected'
            DB::statement("ALTER TABLE investments MODIFY COLUMN status ENUM('pending', 'active', 'withdrawn', 'terminated', 'rejected') DEFAULT 'pending'");
            
            // Add rejection-related fields
            $table->text('rejection_reason')->nullable()->after('status');
            $table->timestamp('rejected_at')->nullable()->after('rejection_reason');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            // Remove the foreign key first
            $table->dropForeign(['rejected_by']);
            
            // Remove the columns
            $table->dropColumn(['rejection_reason', 'rejected_at', 'rejected_by']);
            
            // Revert the status enum
            DB::statement("ALTER TABLE investments MODIFY COLUMN status ENUM('pending', 'active', 'withdrawn', 'terminated') DEFAULT 'pending'");
        });
    }
}; 