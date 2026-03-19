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
            // Add rejection-related fields
            $table->text('rejection_reason')->nullable()->after('status');
            $table->timestamp('rejected_at')->nullable()->after('rejection_reason');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
        });

        // For SQLite compatibility, we can't modify ENUM directly
        // Instead, we'll handle the 'rejected' status in the application layer
        // The status column will accept any string value, and we'll validate in the model
    }

    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            // Remove the foreign key first
            $table->dropForeign(['rejected_by']);
            
            // Remove the columns
            $table->dropColumn(['rejection_reason', 'rejected_at', 'rejected_by']);
        });
    }
}; 