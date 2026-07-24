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
        // Add unique constraint on transactions reference_number per user
        // This prevents duplicate transactions with the same reference
        Schema::table('transactions', function (Blueprint $table) {
            // Add index for better query performance
            $table->index(['user_id', 'transaction_type', 'created_at'], 'idx_user_transactions');
            
            // Add unique constraint for reference numbers per user
            // Note: This allows NULL reference_numbers (legacy data)
            $table->unique(['user_id', 'reference_number'], 'unique_user_transaction_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_user_transactions');
            $table->dropUnique('unique_user_transaction_ref');
        });
    }
};
