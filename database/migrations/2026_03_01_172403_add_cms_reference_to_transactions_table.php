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
        if (Schema::hasTable('transactions')) {
            if (!Schema::hasColumn('transactions', 'cms_expense_id')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->unsignedBigInteger('cms_expense_id')->nullable()->after('module_reference');
                });
            }
            if (!Schema::hasColumn('transactions', 'cms_reference_type')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->string('cms_reference_type', 50)->nullable()->after('cms_expense_id');
                });
            }
            if (!Schema::hasColumn('transactions', 'cms_reference_id')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->unsignedBigInteger('cms_reference_id')->nullable()->after('cms_reference_type');
                });
            }
            
            // Add indexes if columns were added
            if (Schema::hasColumn('transactions', 'cms_expense_id')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('transactions');
                    if (!isset($indexes['idx_cms_expense'])) {
                        $table->index('cms_expense_id', 'idx_cms_expense');
                    }
                });
            }
            if (Schema::hasColumn('transactions', 'cms_reference_type') && Schema::hasColumn('transactions', 'cms_reference_id')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('transactions');
                    if (!isset($indexes['idx_cms_reference'])) {
                        $table->index(['cms_reference_type', 'cms_reference_id'], 'idx_cms_reference');
                    }
                });
            }
        }
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
