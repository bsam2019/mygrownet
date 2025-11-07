<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add performance indexes and additional fields to transactions table
     * to support hybrid payment architecture and handle scale.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Check if columns exist before adding
            if (!Schema::hasColumn('transactions', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('description');
            }
            if (!Schema::hasColumn('transactions', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->after('processed_at')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('transactions', 'notes')) {
                $table->text('notes')->nullable()->after('processed_by');
            }
            
            // Add indexes for performance (Mitigation #1 & #2)
            // Check if indexes don't exist before adding
            if (!$this->indexExists('transactions', 'idx_user_status')) {
                $table->index(['user_id', 'status'], 'idx_user_status');
            }
            if (!$this->indexExists('transactions', 'idx_user_created')) {
                $table->index(['user_id', 'created_at'], 'idx_user_created');
            }
            if (!$this->indexExists('transactions', 'idx_type_status')) {
                $table->index(['transaction_type', 'status'], 'idx_type_status');
            }
            if (!$this->indexExists('transactions', 'idx_payment_status')) {
                $table->index(['payment_method', 'status'], 'idx_payment_status');
            }
            if (!$this->indexExists('transactions', 'idx_created_at')) {
                $table->index(['created_at'], 'idx_created_at');
            }
            if (!$this->indexExists('transactions', 'idx_status_processed')) {
                $table->index(['status', 'processed_at'], 'idx_status_processed');
            }
        });
        
        // Add comment to table for documentation
        DB::statement("ALTER TABLE transactions COMMENT = 'Main financial ledger - Single source of truth for all balances'");
    }
    
    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
        return !empty($indexes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex('idx_user_status');
            $table->dropIndex('idx_user_created');
            $table->dropIndex('idx_type_status');
            $table->dropIndex('idx_payment_status');
            $table->dropIndex('idx_created_at');
            $table->dropIndex('idx_status_processed');
            
            // Drop columns
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['processed_at', 'processed_by', 'notes']);
        });
    }
};

