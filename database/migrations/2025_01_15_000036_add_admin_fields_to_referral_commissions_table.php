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
        Schema::table('referral_commissions', function (Blueprint $table) {
            // Admin adjustment fields
            $table->unsignedBigInteger('adjusted_by')->nullable()->after('paid_at');
            $table->text('adjustment_reason')->nullable()->after('adjusted_by');
            $table->timestamp('adjusted_at')->nullable()->after('adjustment_reason');
            
            // Admin rejection fields
            $table->unsignedBigInteger('rejected_by')->nullable()->after('adjusted_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            
            // Add foreign key constraints
            $table->foreign('adjusted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
            
            // Add indexes for admin queries
            $table->index(['status', 'created_at']);
            $table->index(['commission_type', 'level']);
            $table->index(['adjusted_by', 'adjusted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referral_commissions', function (Blueprint $table) {
            $table->dropForeign(['adjusted_by']);
            $table->dropForeign(['rejected_by']);
            
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['commission_type', 'level']);
            $table->dropIndex(['adjusted_by', 'adjusted_at']);
            
            $table->dropColumn([
                'adjusted_by',
                'adjustment_reason', 
                'adjusted_at',
                'rejected_by',
                'rejected_at'
            ]);
        });
    }
};