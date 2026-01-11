<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            // Change status to include 'changes_requested'
            // Note: SQLite doesn't support modifying enums, so we'll use string
            
            // Rejection/feedback fields
            $table->string('rejection_category')->nullable()->after('rejection_reason');
            $table->json('field_feedback')->nullable()->after('rejection_category');
            
            // Appeal fields
            $table->text('appeal_message')->nullable()->after('field_feedback');
            $table->timestamp('appealed_at')->nullable()->after('appeal_message');
            $table->foreignId('reviewed_by')->nullable()->after('appealed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->dropColumn([
                'rejection_category',
                'field_feedback',
                'appeal_message',
                'appealed_at',
                'reviewed_by',
                'reviewed_at',
            ]);
        });
    }
};
