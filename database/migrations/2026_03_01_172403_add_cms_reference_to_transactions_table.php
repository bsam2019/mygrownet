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
        Schema::table('transactions', function (Blueprint $table) {
            // CMS expense reference
            $table->unsignedBigInteger('cms_expense_id')->nullable()->after('module_reference');
            
            // Generic CMS reference for future use
            $table->string('cms_reference_type', 50)->nullable()->after('cms_expense_id');
            $table->unsignedBigInteger('cms_reference_id')->nullable()->after('cms_reference_type');
            
            // Indexes for performance
            $table->index('cms_expense_id', 'idx_cms_expense');
            $table->index(['cms_reference_type', 'cms_reference_id'], 'idx_cms_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_cms_expense');
            $table->dropIndex('idx_cms_reference');
            $table->dropColumn(['cms_expense_id', 'cms_reference_type', 'cms_reference_id']);
        });
    }
};
